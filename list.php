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

$search_id 		        = GETPOST('search_id');
$search_ref			    = GETPOST('search_ref');
$search_tarif			= GETPOST('search_tarif');
$search_pays			= GETPOST('search_pays');
$search_date_deb		= GETPOST('search_date_deb');
$search_date_fin		= GETPOST('search_date_fin');

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

//entête

print_barre_liste("Voyage", $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, $picto, 0, $newcardbutton, '', $limit, 0, 0, 1);

print '<table class = "liste" width = "100%">' . "\n";
//TITLE


print '<tr class = "liste_titre_filter">';
print '<td><input class="flat" type="text" name="search_id" size="8" value="'.$search_id.'"></td>';
print '<td><input class="flat" type="text" name="search_ref" size="8" value="'.$search_ref.'"></td>';
print '<td><input class="flat" type="text" name="search_tarif" size="8" value="'.$search_tarif.'"></td>';
print '<td><input class="flat" type="text" name="search_pays" size="8" value="'.$search_pays.'"></td>';
print '<td><input class="flat" type="text" name="search_date_deb" size="8" value="'.$search_date_deb.'"></td>';
print '<td><input class="flat" type="text" name="search_date_fin" size="8" value="'.$search_date_fin.'"></td>';
print '<td><button type="submit" class="liste_titre button_search reposition" name="button_search_x" value="x"><span class="fa fa-search"></span></button></td>';
print '</tr>';

print '<tr class = "liste_titre">';


print_liste_field_titre($langs->trans('Id'), $PHP_SELF, '', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('Ref'), $PHP_SELF, '', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('price'), $PHP_SELF, '', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('country'), $PHP_SELF, '', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('startDate'), $PHP_SELF, '', '', $param, '', $sortfield, $sortorder);
print "\n";

print_liste_field_titre($langs->trans('endDate'), $PHP_SELF, '', '', $param, '', $sortfield, $sortorder);
print "\n";

print '</tr>';



$sql = 'SELECT * FROM ' . MAIN_DB_PREFIX.'voyage';

$sql .= ' WHERE 1=1';

if(!empty($search_id)){
    $sql.= ' AND rowid LIKE "%'. $search_id.'%"';
}
if(!empty($search_ref)){
    $sql.= ' AND reference LIKE '.'"%'.$search_ref.'%"';
}
if(!empty($search_tarif)){
    $sql.= ' AND tarif LIKE "%'. $search_tarif.'%"';
}
if(!empty($search_pays)){
    $sql.= ' AND pays LIKE '.'"%'.$search_pays.'%"';
}
if(!empty($search_date_deb)){
    $sql.= ' AND date_deb LIKE '.'"%'.$search_date_deb.'%"';
}
if(!empty($search_date_fin)){
    $sql.= ' AND date_fin LIKE '.'"%'.$search_date_fin.'%"';
}
//print $sql; exit;


    $i = 0;

    $resql = $db->query($sql);
    $num = $db->num_rows($sql);

    while($i<$num){

        $obj = $db->fetch_object($resql);
        $voyage = new Voyage($db);
        $voyage->fetch($obj->rowid);
        print '<tr>';

//	print '<td><a href= " '.dol_buildpath('/voyage/card.php', 1).'?id='. $obj->rowid .' " >' .$obj->reference. '</a></td>';
        print '<td>'.$voyage->getNomUrl(1).'</td>';
        print "\n";

        print '<td>'. $obj->reference .'</td>';
        print "\n";

        print '<td>'. $obj->tarif .'</td>';
        print "\n";

        print '<td>'. $obj->pays .'</td>';
        print "\n";

        print '<td>'. $obj->date_deb .'</td>';
        print "\n";

        print '<td>'. $obj->date_fin .'</td>';
        print "\n";

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
