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

/**
 *	\file		lib/voyage.lib.php
 *	\ingroup	voyage
 *	\brief		This file is an example module library
 *				Put some comments here
 */

/**
 * @return array
 */
function voyageAdminPrepareHead()
{
    global $langs, $conf;

    $langs->load('voyage@voyage');

    $h = 0;
    $head = array();

    $head[$h][0] = dol_buildpath("/voyage/admin/voyage_setup.php", 1);
    $head[$h][1] = $langs->trans("Parameters");
    $head[$h][2] = 'settings';
    $h++;
    $head[$h][0] = dol_buildpath("/voyage/admin/voyage_extrafields.php", 1);
    $head[$h][1] = $langs->trans("ExtraFields");
    $head[$h][2] = 'extrafields';
    $h++;
    $head[$h][0] = dol_buildpath("/voyage/admin/voyage_about.php", 1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    //$this->tabs = array(
    //	'entity:+tabname:Title:@voyage:/voyage/mypage.php?id=__ID__'
    //); // to add new tab
    //$this->tabs = array(
    //	'entity:-tabname:Title:@voyage:/voyage/mypage.php?id=__ID__'
    //); // to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'voyage');

    return $head;
}

/**
 * Return array of tabs to used on pages for third parties cards.
 *
 * @param 	voyage	$object		Object company shown
 * @return 	array				Array of tabs
 */
function voyage_prepare_head(voyage $object)
{
    global $langs, $conf;
    $h = 0;
    $head = array();
    $head[$h][0] = dol_buildpath('/voyage/card.php', 1).'?id='.$object->id;
    $head[$h][1] = $langs->trans("voyageCard");
    $head[$h][2] = 'card';
    $h++;
	
	// Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@voyage:/voyage/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@voyage:/voyage/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf, $langs, $object, $head, $h, 'voyage');
	
	return $head;
}

/**
 * @param Form      $form       Form object
 * @param voyage  $object     voyage object
 * @param string    $action     Triggered action
 * @return string
 */
function getFormConfirmvoyage($form, $object, $action)
{
    global $langs, $user;

    $formconfirm = '';

    if ($action === 'valid' && !empty($user->rights->voyage->write))
    {
        $body = $langs->trans('ConfirmValidatevoyageBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmValidatevoyageTitle'), $body, 'confirm_validate', '', 0, 1);
    }
    elseif ($action === 'accept' && !empty($user->rights->voyage->write))
    {
        $body = $langs->trans('ConfirmAcceptvoyageBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmAcceptvoyageTitle'), $body, 'confirm_accept', '', 0, 1);
    }
    elseif ($action === 'refuse' && !empty($user->rights->voyage->write))
    {
        $body = $langs->trans('ConfirmRefusevoyageBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmRefusevoyageTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'reopen' && !empty($user->rights->voyage->write))
    {
        $body = $langs->trans('ConfirmReopenvoyageBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmReopenvoyageTitle'), $body, 'confirm_refuse', '', 0, 1);
    }
    elseif ($action === 'delete' && !empty($user->rights->voyage->write))
    {
        $body = $langs->trans('ConfirmDeletevoyageBody');
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmDeletevoyageTitle'), $body, 'confirm_delete', '', 0, 1);
    }
    elseif ($action === 'clone' && !empty($user->rights->voyage->write))
    {
        $body = $langs->trans('ConfirmClonevoyageBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmClonevoyageTitle'), $body, 'confirm_clone', '', 0, 1);
    }
    elseif ($action === 'cancel' && !empty($user->rights->voyage->write))
    {
        $body = $langs->trans('ConfirmCancelvoyageBody', $object->ref);
        $formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('ConfirmCancelvoyageTitle'), $body, 'confirm_cancel', '', 0, 1);
    }

    return $formconfirm;
}
