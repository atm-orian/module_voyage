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

if (!class_exists('SeedObject'))
{
	/**
	 * Needed if $form->showLinkedObjectBlock() is call or for session timeout on our module page
	 */
	define('INC_FROM_DOLIBARR', true);
	require_once dirname(__FILE__).'/../config.php';
}


class Voyage extends SeedObject
{
    /**
     * Canceled status
     */

	/** @var string $table_element Table name in SQL */
	public $table_element = 'voyage';

	/** @var string $element Name of the element (tip for better integration in Dolibarr: this value should be the reflection of the class name with ucfirst() function) */
	public $element = 'voyage';

	/** @var int $isextrafieldmanaged Enable the fictionalises of extrafields */
    public $isextrafieldmanaged = 1;

    /** @var int $ismultientitymanaged 0=No test on entity, 1=Test with field entity, 2=Test with link by societe */
    public $ismultientitymanaged = 1;



    /** @var string $reference Object reference */
	public $reference;

    /** @var int $entity Object entity */
	public $entity;

	/** @var int $status Object status */
	public $status;

    /** @var string $label Object label */
    public $label;

    /** @var string $description Object description */
    public $description;



	/** @var string $tarif Object price */
	public $tarif;

	/** @var string $pays Object country */
	public $pays;

	/** @var string $date_deb Object startDateAndHour */
	public $date_deb;

	/** @var string $date_fin Object endDateAndHour */
	public $date_fin;

	/**
	 *  'type' is the field format.
	 *  'label' the translation key.
	 *  'enabled' is a condition when the field must be managed.
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'default' is a default value for creation (can still be replaced by the global setup of default values)
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'position' is the sort order of field.
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' is the CSS style to use on field. For example: 'maxwidth200'
	 *  'help' is a string visible as a tooltip on field
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 */

	public $fields = array(

		'reference' => array(
			'type' => 'varchar(50)',
			'length' => 50,
			'label' => 'Ref',
			'enabled' => 1,
			'visible' => 1,
			'notnull' => 1,
			'showoncombobox' => 1,
			'index' => 1,
			'position' => 10,
			'searchall' => 1,
			'comment' => 'Reference of object'
		),

		//objet tarif
		'tarif' => array(
			'type' => 'int',
			'length' => 10,
			'label' => 'price',
			'enabled' => 1,
			'visible' => 1,
			'notnull' => 1,
			'index' => 1,
			'position' => 15

		),

		'pays' => array(),

		'date_deb' => array(
			'type' => 'date',
			'label' => 'date_deb',
			'enabled' => 1,
			'visible' => 1,
			'notnull' => 0,
			'position' => 16
		),

		'date_fin' => array(
			'type' => 'date',
			'label' => 'date_deb',
			'enabled' => 1,
			'visible' => 1,
			'notnull' => 0,
			'position' => 17
		),

		'entity' => array(
			'type' => 'integer',
			'label' => 'Entity',
			'enabled' => 1,
			'visible' => 0,
			'default' => 1,
			'notnull' => 1,
			'index' => 1,
			'position' => 20
		),

		'fk_soc' => array(
			'type' => 'integer:Societe:societe/class/societe.class.php',
			'label' => 'ThirdParty',
			'visible' => 1,
			'enabled' => 1,
			'position' => 50,
			'index' => 1,
			'help' => 'LinkToThirparty'
		),

		'description' => array(
			'type' => 'text', // or html for WYSWYG
			'label' => 'Description',
			'enabled' => 1,
			'visible' => -1, //  un bug sur la version 9.0 de Dolibarr necessite de mettre -1 pour ne pas apparaitre sur les listes au lieu de la valeur 3
			'position' => 60
		),

//		'date_creation' => array(
//			'type'=>'datetime',
//			'label'=>'DateCreation',
//			'enabled'=>1,
//			'position'=>500,
//			'notnull'=>1,
//			'visible'=>-2,
//			'position'=>80),
//
//		'tms' =>array(
//			'type'=>'timestamp',
//			'label'=>'DateModification',
//			'enabled'=>1,
//			'visible'=>-1,
//			'notnull'=>1,
//			'position'=>85)
	);



    /**
     * voyage constructor.
     * @param DoliDB    $db    Database connector
     */
    public function __construct($db)
    {
		global $conf;

        parent::__construct($db);

		$this->init();

		$this->entity = $conf->entity;
    }

    /**
     * @param User $user User object
     * @return int
     */
    public function save($user)
    {
        return $this->create($user);
    }



    /**
     * @see cloneObject
     * @return void
     */
    public function clearUniqueFields()
    {
        $this->ref = 'Copy of '.$this->ref;
    }


    /**
     * @param User $user User object
     * @return int
     */
    public function delete(User &$user)
    {
        $this->deleteObjectLinked();

        unset($this->fk_element); // avoid conflict with standard Dolibarr comportment
        return parent::delete($user);
    }

    /**
     * @return string
     */
    public function getRef()
    {
		if (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))
		{
			return $this->getNextRef();
		}

		return $this->ref;
    }

    /**
     * @return string
     */
    private function getNextRef()
    {
		global $db,$conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

		$mask = !empty($conf->global->VOYAGE_REF_MASK) ? $conf->global->VOYAGE_REF_MASK : 'MM{yy}{mm}-{0000}';
		$ref = get_next_value($db, $mask, 'voyage', 'ref');

		return $ref;
    }



    /**
     * @param int    $withpicto     Add picto into link
     * @param string $moreparams    Add more parameters in the URL
     * @return string
     */
    public function getNomUrl($withpicto = 0, $moreparams = '')
    {
		global $langs;

        $result='';
        $label = '<u>' . $langs->trans("Showvoyage") . '</u>';
        if (! empty($this->ref)) $label.= '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;

        $linkclose = '" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
        $link = '<a href="'.dol_buildpath('/voyage/card.php', 1).'?id='.$this->id.urlencode($moreparams).$linkclose;

        $linkend='</a>';

        $picto='generic';
//        $picto='voyage@voyage';

        if ($withpicto) $result.=($link.img_object($label, $picto, 'class="classfortooltip"').$linkend);
        if ($withpicto && $withpicto != 2) $result.=' ';

        $result.=$link.$this->ref.$linkend;

        return $result;
    }

    /**
     * @param int       $id             Identifiant
     * @param null      $ref            Ref
     * @param int       $withpicto      Add picto into link
     * @param string    $moreparams     Add more parameters in the URL
     * @return string
     */
    public static function getStaticNomUrl($id, $ref = null, $withpicto = 0, $moreparams = '')
    {
		global $db;

		$object = new voyage($db);
		$object->fetch($id, false, $ref);

		return $object->getNomUrl($withpicto, $moreparams);
    }


    /**
     * @param int $mode     0=Long label, 1=Short label, 2=Picto + Short label, 3=Picto, 4=Picto + Long label, 5=Short label + Picto, 6=Long label + Picto
     * @return string
     */
    public function getLibStatut($mode = 0)
    {
        return self::LibStatut($this->status, $mode);
    }
}


//class voyageDet extends SeedObject
//{
//    public $table_element = 'voyagedet';
//
//    public $element = 'voyagedet';
//
//
//    /**
//     * voyageDet constructor.
//     * @param DoliDB    $db    Database connector
//     */
//    public function __construct($db)
//    {
//        $this->db = $db;
//
//        $this->init();
//    }
//}