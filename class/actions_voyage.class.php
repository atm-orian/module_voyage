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
 * \file    class/actions_voyage.class.php
 * \ingroup voyage
 * \brief   This file is an example hook overload class file
 *          Put some comments here
 */

/**
 * Class Actionsvoyage
 */
class Actionsvoyage
{
    /**
     * @var DoliDb        Database handler (result of a new DoliDB)
     */
    public $db;
    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public $results = array();
    /**
     * @var string String displayed by executeHook() immediately after return
     */
    public $resprints;
    /**
     * @var array Errors
     */
    public $errors = array();

    /**
     * Constructor
     * @param DoliDB $db Database connector
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Overloading the doActions function : replacing the parent's function with the one below
     *
     * @param array()         $parameters     Hook metadatas (context, etc...)
     * @param CommonObject $object The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
     * @param string $action Current action (if set). Generally create or edit or null
     * @param HookManager $hookmanager Hook manager propagated to allow calling another hook
     * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
     */
    public function doActions($parameters, &$object, &$action, $hookmanager) {
        $error = 0; // Error counter
        $myvalue = 'test'; // A result value

        echo "action: " . $action;

        if (in_array('somecontext', explode(':', $parameters['context']))) {
            // do something only for the context 'somecontext'
        }

        if (! $error) {
            $this->results = array('myreturn' => $myvalue);
            $this->resprints = 'A text to show';

            return 0; // or return 1 to replace standard code
        } else {
            $this->errors[] = 'Error message';

            return -1;
        }
    }

    public function addMoreActionsButtons($parameters, &$object, &$action, $hookmanager){

        global $conf, $db, $langs ;

        if (in_array('productcard', explode(':', $parameters['context'])))
        {
            print '<a class="butAction" href="'.DOL_URL_ROOT .'/custom/voyage/card.php?action=create&idProduct='.$object->id.'">'.$langs->trans("CreateVoyage").'</a>'."\n";
        }

        return 0;
    }
}
