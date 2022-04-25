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
// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root caluclated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
dol_include_once('voyage/class/voyage.class.php');
dol_include_once('voyage/lib/voyage.lib.php');

if(empty($user->rights->voyage->read)) accessforbidden();

$langs->load('voyage@voyage');

$action = GETPOST('action');
$id = GETPOST('id', 'int');
$ref = GETPOST('ref');
$cancel = GETPOST('cancel', 'alpha');
$ArrayLabel = Voyage::getStaticArrayTag();

if($id){
    $ArrayLabelPreselected = Voyage::getStaticArrayPreselectedTag($id);
}



$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'voyagecard';   // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');

$object = new voyage($db);

if (!empty($id) || !empty($ref)) $object->fetch($id, true, $ref);

$hookmanager->initHooks(array('voyagecard', 'globalcard'));


if ($object->isextrafieldmanaged)
{
    $extrafields = new ExtraFields($db);

    $extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
    $search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');
}

// Initialize array of search criterias
//$search_all=trim(GETPOST("search_all",'alpha'));
//$search=array();
//foreach($object->fields as $key => $val)
//{
//    if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
//}

/*
 * Actions
 */

$parameters = array('id' => $id, 'ref' => $ref);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

// Si vide alors le comportement n'est pas remplacé
if (empty($reshook))
{

    if ($cancel)
    {
        if (! empty($backtopage))
        {
            header("Location: ".$backtopage);
            exit;
        }
        $action='';
    }

    // For object linked
    include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once

    $error = 0;

//    var_dump($action);exit;
    $voyage = new Voyage($db);

	switch ($action) {
		case 'add':
//			var_dump($_POST);exit;

			$voyage->reference 				= GETPOST('ref');
            if (empty($voyage->reference)) {
//                var_dump($voyage->reference);
                setEventMessages($langs->trans('EmptyRef'), array(), 'errors');
                $action = "create";
                $error++;
            }


			$voyage->tarif 					= GETPOST('tarif','int');

//            var_dump(is_double($voyage->tarif), $voyage->tarif);exit;

//             (!is_double($voyage->tarif))
//            {
//                setEventMessages($langs->trans('PriceError'), array(), 'errors');
//                $action = 'create';
//                $error++;
//            }


			$voyage->pays 					= GETPOST('pays', 'alpha');

			$date_d = GETPOST('date_deb');
            if(!empty($date_d)){
                $date_dConvert = DateTime::createFromFormat('d/m/Y', $date_d);
                $voyage->date_deb				= $date_dConvert->format('Y-m-d');
            }




			$date_f = GETPOST('date_fin');
            if(!empty($date_f)){
                $date_fConvert= DateTime::createFromFormat('d/m/Y',$date_f);
                $voyage->date_fin				= $date_fConvert->format('Y-m-d');
            }


            if ($error > 0)
            {
                header('Location: '.dol_buildpath('/voyage/card.php', 1).'?action=create');
                exit;
            }
			$res = $voyage->save($user);


            $rowidVoyage = $voyage->id;
            $rowidTag = GETPOST('tag','array');
//var_dump($rowidTag);exit;
            if(!empty(GETPOST('tag','alpha')))
            {
                foreach ($rowidTag as $valueRowidTag){
                    $voyage->setLabelTag($rowidVoyage,$valueRowidTag);
                }
            }

            //TARIF

            if (empty($voyage->tarif) && !(empty($rowidTag))){
                $voyage->setTarif($rowidVoyage,$rowidTag);
            }
            elseif(empty($voyage->tarif) && (empty($rowidTag))){
                $voyage->tarif = $conf->global->VOYAGE_TARIF;
                $voyage->save($user);
            }

            //var_dump($voyage);exit;

			header('Location: '.dol_buildpath('/voyage/card.php', 1).'?id='.$voyage->id);

			break;
		case 'update':
            //var_dump($_REQUEST);exit;

            $voyage->setValues($_REQUEST); // Set standard attributes
//            var_dump($_REQUEST);exit;


            $rowidVoyage = $voyage->id;
            $rowidTag = GETPOST('tag','array');

            if(!empty(GETPOST('tag','alpha')))
            {
                $voyage->deleteVoyage($rowidVoyage);
                foreach ($rowidTag as $valueRowidTag){
                    $voyage->setLabelTag($rowidVoyage,$valueRowidTag);

                }
            }

            if ($voyage->isextrafieldmanaged)
            {
                $ret = $extrafields->setOptionalsFromPost($extralabels, $voyage);
                if ($ret < 0) $error++;
            }

            //var_dump(array($object, $voyage));exit;
            if (empty($voyage->reference)) {
//                var_dump($voyage->reference);
                setEventMessages($langs->trans('EmptyRef'), array(), 'errors');
                $action = "edit";
                $error++;
            }

//			$object->date_other = dol_mktime(GETPOST('starthour'), GETPOST('startmin'), 0, GETPOST('startmonth'), GETPOST('startday'), GETPOST('startyear'));

			// Check parameters

			// ...
			if ($error > 0)
			{
				$action = 'edit';
				break;
			}

			$res = $voyage->save($user);
            if ($res < 0)
            {
                setEventMessage($voyage->errors, 'errors');
                if (empty($voyage->id)) $action = 'create';
                else $action = 'edit';
            }
            else
            {
                header('Location: '.dol_buildpath('/voyage/card.php', 1).'?id='.$voyage->id);
                exit;
            }

        case 'update_extras':

            $object->oldcopy = dol_clone($object);

            // Fill array 'array_options' with data from update form
            $ret = $extrafields->setOptionalsFromPost($extralabels, $object, GETPOST('attribute', 'none'));
            if ($ret < 0) $error++;

            if (! $error)
            {
                $result = $object->insertExtraFields('VOYAGE_MODIFY');
                if ($result < 0)
                {
                    setEventMessages($object->error, $object->errors, 'errors');
                    $error++;
                }
            }

            if ($error) $action = 'edit_extras';
            else
            {
                header('Location: '.dol_buildpath('/voyage/card.php', 1).'?id='.$object->id);
                exit;
            }
            break;
		case 'confirm_clone':
			$object->cloneObject($user);

			header('Location: '.dol_buildpath('/voyage/card.php', 1).'?id='.$object->id);
			exit;

		case 'modif':
		case 'reopen':
			if (!empty($user->rights->voyage->write)) $object->setDraft($user);

			break;
		case 'confirm_validate':
			if (!empty($user->rights->voyage->write)) $object->setValid($user);


			header('Location: '.dol_buildpath('/voyage/card.php', 1).'?id='.$object->id);
			exit;

		case 'confirm_delete':
//			var_dump($object); exit;
			if (!empty($user->rights->voyage->delete)) {
				$res = $object->delete($user);
				if($res <= 0) setEventMessages($object->error, $object->errors, 'errors');
			}

			header('Location: '.dol_buildpath('/voyage/list.php', 1));
			exit;

		// link from llx_element_element
		case 'dellink':
			$object->deleteObjectLinked(null, '', null, '', GETPOST('dellinkid'));
			header('Location: '.dol_buildpath('/voyage/card.php', 1).'?id='.$object->id);
			exit;

	}
}


/**
 * View
 */

$form = new Form($db);

$title=$langs->trans('voyage');
llxHeader('', $title);

if ($action == 'create')
{

//	var_dump($_POST);exit;
    print load_fiche_titre($langs->trans('Newvoyage'), '', 'voyage@voyage');

    print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="add">';
    print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

    dol_fiche_head(array(), '');

    print '<table class="border centpercent">'."\n";

    // Common attributes

	print '<tr><td class="fieldrequired">'.$langs->trans("Ref").'</td><td><input name="ref" class="minwidth300 maxwidth400onsmartphone" maxlength="255" value="'.dol_escape_htmltag(GETPOST('label', $label_security_check)).'"></td></tr>';

	print '<tr><td >'.$langs->trans("Price").'</td> <td> <input name="tarif" class="" maxlength="255" value="'.dol_escape_htmltag(GETPOST('price', $label_security_check)).'"></td> </tr>';

	print '<tr><td >'.$langs->trans("Country").'</td><td>';
    //print '<td> <input name="pays" class="" maxlength="255" value="'.dol_escape_htmltag(GETPOST('country', $label_security_check)).'"></td> </tr>';
    print $form->select_country('', 'pays', '', 0, 'minwidth300 widthcentpercentminusx maxwidth500');
    //var_dump($form->select_country());exit;
    //$selected =
    //(GETPOSTISSET('pays') ? GETPOST('pays') : $voyage->pays)
    print '</td></tr>';

	// Date de départ
	print '<tr><td class="">'.$langs->trans('StartDate').'</td><td>';
	print $form->selectDate('','date_deb','','');
	print '</td></tr>';

	// Date d'arrivée
	print '<tr><td class="">'.$langs->trans('EndDate').'</td><td>';
	print $form->selectDate('','date_fin','','');
	print '</td></tr>';


    print '<tr><td class="">'.$langs->trans('Tag').'</td><td>';

    print Form::multiselectarray('tag',$ArrayLabel, GETPOST('tag', 'array'));

    print '</td></tr>';



	// Other attributes
    print '</table>'."\n";

    dol_fiche_end();

    print '<div class="center">';
    print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans('Create')).'">';
    print '&nbsp; ';
    print '<input type="'.($backtopage?"submit":"button").'" class="button" name="cancel" value="'.dol_escape_htmltag($langs->trans('Cancel')).'"'.($backtopage?'':' onclick="javascript:history.go(-1)"').'>';	// Cancel for create does not post form if we don't know the backtopage
    print '</div>';

    print '</form>';
}
else
{
    if (empty($object->id))
    {
        $langs->load('errors');
        print $langs->trans('ErrorRecordNotFound');
    }
    else
    {
        if (!empty($object->id) && $action === 'edit')
        {
            print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
            print '<input type="hidden" name="id" value="'.$object->id.'">';

            $head = voyage_prepare_head($object);
            $picto = 'voyage@voyage';
            dol_fiche_head($head, 'card', $langs->trans('voyage'), 0, $picto);

            print '<table class="border centpercent">'."\n";

            // Common attributes
            include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_edit.tpl.php';

            print '<tr><td > Catégorie </td>';

            print '<td>'.Form::multiselectarray('tag',$ArrayLabel, $ArrayLabelPreselected).'</td>';
            print '</tr>';

            // Other attributes
            include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_edit.tpl.php';

            print '</table>';

            dol_fiche_end();

            print '<div class="center"><input type="submit" class="button" name="save" value="'.$langs->trans('Save').'">';
            print ' &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
            print '</div>';

            print '</form>';
        }
        elseif ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
        {
            $head = voyage_prepare_head($object);
            $picto = 'voyage@voyage';
            dol_fiche_head($head, 'card', $langs->trans('voyage'), -1, $picto);

            $formconfirm = getFormConfirmvoyage($form, $object, $action);
            if (!empty($formconfirm)) print $formconfirm;


            $linkback = '<a href="' .dol_buildpath('/voyage/list.php', 1) . '?restore_lastsearch_values=1">' . $langs->trans('BackToList') . '</a>';

            $morehtmlref='<div class="refidno">';
            /*
            // Ref bis
            $morehtmlref.=$form->editfieldkey("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->voyage->write, 'string', '', 0, 1);
            $morehtmlref.=$form->editfieldval("RefBis", 'ref_client', $object->ref_client, $object, $user->rights->voyage->write, 'string', '', null, null, '', 1);
            // Thirdparty
            $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $soc->getNomUrl(1);
            */
            $morehtmlref.='</div>';


            $morehtmlstatus.=''; //$object->getLibStatut(2); // pas besoin fait doublon

            $shownav = 1;

            dol_banner_tab($object, '', $linkback, $shownav, '');

            print '<div class="fichecenter">';

            print '<div class="fichehalfleft">'; // Auto close by commonfields_view.tpl.php
            print '<div class="underbanner clearboth"></div>';
            print '<table class="border tableforfield" width="100%">'."\n";

            // Common attributes
            //$keyforbreak='fieldkeytoswithonsecondcolumn';

            include DOL_DOCUMENT_ROOT . '/core/tpl/commonfields_view.tpl.php';

            print '<tr><td > Catégorie </td>';

                //var_dump($id, $voyage);exit;
            print '<td id="product_extras_test_12" class="valuefield product_extras_test wordbreak"><div class="select2-container-multi-dolibarr" style="width: 90%;" ><ul class="select2-choices-dolibarr">';
                $valueRowidTag = $voyage->getValueRowidTag($id);
                if (!empty($valueRowidTag)){
                    foreach ($valueRowidTag as $row){
                        print '<li class="select2-search-choice-dolibarr noborderoncategories" style="background: #bbb">'.$row. '</li>';
                    }
                }

                print '</ul></div></td></tr>';


 //var_dump($object);exit;


            // Other attributes
            include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

            print '</table>';

            print '</div></div>'; // Fin fichehalfright & ficheaddleft
            print '</div>'; // Fin fichecenter

            print '<div class="clearboth"></div><br />';

            print '<div class="tabsAction">'."\n";
            $parameters=array();
            $reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action);    // Note that $action and $object may have been modified by hook
            if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

            if (empty($reshook))
            {
                // Send
                //        print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">' . $langs->trans('SendMail') . '</a>'."\n";

                // Modify
//                if (!empty($user->rights->voyage->write))
//                {
//                    if ($object->status !== voyage::STATUS_CANCELED)
//                    {
//                        // Modify
//                        if ($object->status !== voyage::STATUS_ACCEPTED) print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=edit">'.$langs->trans("voyageModify").'</a></div>'."\n";
//                        // Clone
//                        print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=clone">'.$langs->trans("voyageClone").'</a></div>'."\n";
//                    }
//
//                    // Valid
//                    if ($object->status === voyage::STATUS_DRAFT) print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=valid">'.$langs->trans('voyageValid').'</a></div>'."\n";
//
//                    // Accept
//                    if ($object->status === voyage::STATUS_VALIDATED) print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=accept">'.$langs->trans('voyageAccept').'</a></div>'."\n";
//                    // Refuse
//                    if ($object->status === voyage::STATUS_VALIDATED) print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=refuse">'.$langs->trans('voyageRefuse').'</a></div>'."\n";
//
//
//                    // Reopen
//                    if ($object->status === voyage::STATUS_ACCEPTED || $object->status === voyage::STATUS_REFUSED) print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=reopen">'.$langs->trans('voyageReopen').'</a></div>'."\n";
//                    // Cancel
//                    if ($object->status === voyage::STATUS_VALIDATED) print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=cancel">'.$langs->trans("voyageCancel").'</a></div>'."\n";
//                }
//                else
//                {
//                    if ($object->status !== voyage::STATUS_CANCELED)
//                    {
//                        // Modify
//                        if ($object->status !== voyage::STATUS_ACCEPTED) print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("voyageModify").'</a></div>'."\n";
//                        // Clone
//                        print '<div class="inline-block divButAction"><a class="butAction" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("voyageClone").'</a></div>'."\n";
//                    }
//
//                    // Valid
//                    if ($object->status === voyage::STATUS_DRAFT) print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('voyageValid').'</a></div>'."\n";
//
//                    // Accept
//                    if ($object->status === voyage::STATUS_VALIDATED) print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">'.$langs->trans('voyageAccept').'</a></div>'."\n";
//                    // Refuse
//                    if ($object->status === voyage::STATUS_VALIDATED) print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">'.$langs->trans('voyageRefuse').'</a></div>'."\n";
//
//                    // Reopen
//                    if ($object->status === voyage::STATUS_ACCEPTED || $object->status === voyage::STATUS_REFUSED) print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans('voyageReopen').'</a></div>'."\n";
//                    // Cancel
//                    if ($object->status === voyage::STATUS_VALIDATED) print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("voyageCancel").'</a></div>'."\n";
//                }

                if (!empty($user->rights->voyage->delete))
                {
                    print '<div class="inline-block divButAction"><a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans("voyageDelete").'</a></div>'."\n";
                }
                else
                {
                    print '<div class="inline-block divButAction"><a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotEnoughPermissions")).'">'.$langs->trans("voyageDelete").'</a></div>'."\n";
                }
				if ($user->rights->societe->creer) {
					print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit">'.$langs->trans("Modify").'</a>'."\n";
				}
            }
            print '</div>'."\n";

            print '<div class="fichecenter"><div class="fichehalfleft">';
            $linktoelem = $form->showLinkToObjectBlock($object, null, array($object->element));
            $somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

            print '</div><div class="fichehalfright"><div class="ficheaddleft">';

            // List of actions on element
            include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
            $formactions = new FormActions($db);
            $somethingshown = $formactions->showactions($object, $object->element, $socid, 1);

            print '</div></div></div>';

            dol_fiche_end(-1);
        }
    }
}


llxFooter();
$db->close();
