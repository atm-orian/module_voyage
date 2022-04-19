<?php
/* Copyright (C) 2022 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require 'config.php';

dol_include_once('voyage/class/voyage.class.php');

if(empty($user->rights->voyage->read)) accessforbidden();

$langs->load('abricot@abricot');
$langs->load('voyage@voyage');

//INIT FILTER VAR
$search_id 		        = trim(GETPOST('search_id', 'int'));
$search_ref			    = trim(GETPOST('search_ref', 'string'));
$search_tarif			= trim(GETPOST('search_tarif', 'double'));
$search_pays			= trim(GETPOST('search_pays', 'string'));

$search_date_deb		= trim(GETPOST('search_date_deb', 'string'));
$search_date_dConvert   = DateTime::createFromFormat('d/m/Y', $search_date_deb);
if(!empty($search_date_dConvert)){
    $search_date_dConvertTimestamp = $search_date_dConvert->getTimestamp();

}


$search_date_fin		= trim(GETPOST('search_date_fin', 'string'));
$search_date_fConvert   = DateTime::createFromFormat('d/m/Y', $search_date_fin);
if(!empty($search_date_fConvert)){
    $search_date_fConvertTimestamp = $search_date_fConvert->getTimestamp();

}


$massaction = GETPOST('massaction', 'alpha');
$confirmmassaction = GETPOST('confirmmassaction', 'alpha');
$toselect = GETPOST('toselect', 'array');

$object = new voyage($db);

$hookmanager->initHooks(array('voyagelist'));

if ($object->isextrafieldmanaged)
{
    $extrafields = new ExtraFields($db);
    $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
}

/*
 * Actions
 */

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (!GETPOST('confirmmassaction', 'alpha') && $massaction != 'presend' && $massaction != 'confirm_presend')
{
    $massaction = '';
}

/*
 * View
 */
llxHeader('', $langs->trans('voyageList'), '', '');


//BUTTON RESET
if(GETPOST('button_removefilter_x','alpha')){
    $search_id = '';
    $search_date_deb='';
    $search_date_fin='';
    $search_ref='';
    $search_pays='';
    $search_tarif='';

    $search_date_dConvertTimestamp = '-1';
    $search_date_fConvertTimestamp = '-1';
}


//$type = GETPOST('type');
//if (empty($user->rights->voyage->all->read)) $type = 'mine';

// TODO ajouter les champs de son objet que l'on souhaite afficher
$keys = array_keys($object->fields);
$fieldList = 't.'.implode(', t.', $keys);
if (!empty($object->isextrafieldmanaged))
{
    $keys = array_keys($extralabels);
	if(!empty($keys)) {
		$fieldList .= ', et.' . implode(', et.', $keys);
	}
}

$sql = 'SELECT '.$fieldList;

// Add fields from hooks
$parameters=array('sql' => $sql);
$reshook=$hookmanager->executeHooks('printFieldListSelect', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

$sql.= ' FROM '.MAIN_DB_PREFIX.'voyage t ';


// Add where from hooks
$parameters=array('sql' => $sql);
$reshook=$hookmanager->executeHooks('printFieldListWhere', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;

$formcore = new TFormCore($_SERVER['PHP_SELF'], 'form_list_voyage', 'GET');

$nbLine = GETPOST('limit');
if (empty($nbLine)) $nbLine = !empty($user->conf->MAIN_SIZE_LISTE_LIMIT) ? $user->conf->MAIN_SIZE_LISTE_LIMIT : $conf->global->MAIN_SIZE_LISTE_LIMIT;

// List configuration
$picto = 'voyage@voyage';


//FILTER REQUEST
$sql = 'SELECT v.*, c.label as labelpays FROM ' . MAIN_DB_PREFIX.'voyage v';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_country c ON (v.pays = c.rowid) ';

$sql .= ' WHERE 1=1';

if(!empty($search_id)){
    $sql.= ' AND v.rowid LIKE "%'. $search_id.'%"';
}
if(!empty($search_ref)){
    $sql.= ' AND v.reference LIKE '.'"%'.$search_ref.'%"';
}
if(!empty($search_tarif)){
    $sql.= ' AND v.tarif LIKE "%'. $search_tarif.'%"';
}
if(!empty($search_pays)){
    $sql.= ' AND v.pays LIKE '.'"%'.$search_pays.'%"';
}

//var_dump($search_date_dConvert);exit;

if(!empty($search_date_deb)){
    $sql.= ' AND v.date_deb LIKE '.'"%'.$search_date_dConvert->format('Y-m-d').'%"';
}
if(!empty($search_date_fin)){
    $sql.= ' AND v.date_fin LIKE '.'"%'.$search_date_fConvert->format('Y-m-d').'%"';
}


//LIMIT AND OFFSET PAGE
$i = 0;
$limit = GETPOST('limit', 'int') ? GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');

$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page < 0 || GETPOST('button_search', 'alpha') || GETPOST('button_removefilter', 'alpha')) {
    $page = 0;
}     // If $page is not defined, or '' or -1 or if we click on clear filters or if we select empty mass action
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
    $result = $db->query($sql);
    $nbtotalofrecords = $db->num_rows($result);
    if (($page * $limit) > $nbtotalofrecords) {	// if total resultset is smaller then paging size (filtering), goto and load page 0
        $page = 0;
        $offset = 0;
    }
}
$sql .= $db->order($sortfield, $sortorder);
$sql .= $db->plimit($limit + 1, $offset);
$resql = $db->query($sql);
$num = $db->num_rows($sql);


//PARAM SAVE URL
$param = '';
if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) {
    $param .= '&contextpage='.urlencode($contextpage);
}
if ($limit > 0 && $limit != $conf->liste_limit) {
    $param .= '&limit='.urlencode($limit);
}
if ($search_id){
    $param .= '&search_id='.urlencode($search_id);
}
if ($search_ref) {
    $param = '&search_ref='.urlencode($search_ref);
}
if ($search_pays){
    $param = '&search_pays='.urlencode($search_pays);
}
if ($search_tarif) {
    $param = '&search_tarif='.urlencode($search_tarif);
}
if ($search_date_deb){
    $param = '&search_date_deb='.urlencode($search_date_deb);
}
if ($search_date_fin){
    $param = '&search_date_fin='.urlencode($search_date_fin);
}

$newcardbutton = '<a class="btnTitle btnTitlePlus" href="'.dol_buildpath('/voyage/card.php?action=create', 1).'" title="Nouveau Voyage"><span class="fa fa-plus-circle valignmiddle btnTitle-icon"></span></a>';
//var_dump(GETPOST('limit', 'int'));


print_barre_liste("Voyage", $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, $picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

//LEADING
print '<table><tr>';
print '</tr> </table>';


//FILTER INPUT
print '<table class = "liste" width = "100%">' . "\n";
print '<tr class = "liste_titre_filter">';

print '<td><input class="flat" type="text" name="search_id" size="8" value="'.$search_id.'"></td>';
print '<td><input class="flat" type="text" name="search_ref" size="8" value="'.$search_ref.'"></td>';
print '<td><input class="flat" type="text" name="search_tarif" size="8" value="'.$search_tarif.'"></td>';
//print '<td><input class="flat" type="text" name="search_pays" size="8" value="'.$search_pays.'"></td>';
print '<td>'. $form->select_country('', 'search_pays', '', 0, 'minwidth300 widthcentpercentminusx maxwidth500').'</td>';
print '<td>'. $form->selectDate($search_date_dConvertTimestamp,'search_date_deb','','') .'</td>';
print '<td>'. $form->selectDate($search_date_fConvertTimestamp,'search_date_fin','','');

//FILTER BUTTON
print '<button type="submit" class="liste_titre button_search reposition" name="button_search_x" value="x"><span class="fa fa-search"></span></button>';
print '<button type="submit" class="liste_titre button_removefilter reposition" name="button_removefilter_x" value="x"><span class="fa fa-remove"></span></button></td>';
print '</tr>';


//TITLE
print '<tr class = "liste_titre">';

print_liste_field_titre($langs->trans('Id'), $_SERVER["PHP_SELF"], 'v.rowid', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('Ref'), $_SERVER["PHP_SELF"], 'v.reference', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('price'), $_SERVER["PHP_SELF"], 'v.tarif', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('country'), $_SERVER["PHP_SELF"], 'v.pays', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('startDate'), $_SERVER["PHP_SELF"], 'v.date_deb', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('endDate'), $_SERVER["PHP_SELF"], 'v.date_fin', '', $param, '', $sortfield, $sortorder);
print "\n";

print '</tr>';


//PRINT REQUEST
    while($i<min($num, $limit)){

        $obj = $db->fetch_object($resql);
        $voyage = new Voyage($db);
        $voyage->fetch($obj->rowid);

        print '<tr>';

        print '<td>'.$voyage->getNomUrl(1).'</td>';
        print "\n";

        print '<td>'. $obj->reference .'</td>';
        print "\n";

        print '<td>'. $obj->tarif .'</td>';
        print "\n";

        print '<td>'. $obj->labelpays .'</td>';
        print "\n";


        if(!empty($obj->date_deb)){
            $date_dConvertList = DateTime::createFromFormat('Y-m-d', $obj->date_deb);
            print '<td>'. $date_dConvertList->format('d/m/Y') .'</td>';
            print "\n";
        }
        else{
            print '<td>'. $obj->date_deb.'</td>';
            print "\n";
        }

        if(!empty($obj->date_fin)){
            $date_fConvertList = DateTime::createFromFormat('Y-m-d', $obj->date_fin);
            print '<td>'. $date_fConvertList->format('d/m/Y') .'</td>';
            print "\n";
        }
        else {
            print '<td>'. $obj->date_fin.'</td>';
            print "\n";
        }

        print '</tr>';
        $i++;
    }



$parameters=array('sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;


$formcore->end_form();

llxFooter('');
$db->close();

/**
 * TODO remove if unused
 */
function _getObjectNomUrl($id, $ref)
{
	global $db;

	$o = new voyage($db);
	$res = $o->fetch($id, false, $ref);
	if ($res > 0)
	{
		return $o->getNomUrl(1);
	}

	return '';
}

/**
 * TODO remove if unused
 */
function _getUserNomUrl($fk_user)
{
	global $db;

	$u = new User($db);
	if ($u->fetch($fk_user) > 0)
	{
		return $u->getNomUrl(1);
	}

	return '';
}
