<?php
/**
 * 
 * @package externaldata
 *
 * @uses DBField::scaffoldFormField()
 * @uses ExternalDataObject::fieldLabels()
 */
class ExternalDataFormScaffolder extends Object {
	
	/**
	 * @var DataObject $obj The object defining the fields to be scaffolded
	 * through its metadata like $db, $searchable_fields, etc.
	 */
	protected $obj;
	
	/**
	 * @var boolean $tabbed Return fields in a tabset, with all main fields in the path "Root.Main", 
	 * relation fields in "Root.<relationname>" (if {@link $includeRelations} is enabled).
	 */
	public $tabbed = false;
	
	/**
	 * @var boolean $ajaxSafe 
	 */
	public $ajaxSafe = false;
	
	/**
	 * @var array $restrictFields Numeric array of a field name whitelist.
	 * If left blank, all fields from {@link DataObject->db()} will be included.
	 * 
	 * @todo Implement restrictions for has_many and many_many relations.
	 */
	public $restrictFields;
	
	/**
	 * @var array $fieldClasses Optional mapping of fieldnames to subclasses of {@link FormField}.
	 * By default the scaffolder will determine the field instance by {@link DBField::scaffoldFormField()}.
	 * 
	 * @todo Implement fieldClasses for has_many and many_many relations
	 */
	public $fieldClasses;
	
	/**
	 * @var boolean $includeRelations Include has_one, has_many and many_many relations
	 */
	public $includeRelations = false;
	
	/**
	 * @param DataObject $obj
	 * @param array $params
	 */
	public function __construct($obj) {
		$this->obj = $obj;
		parent::__construct();
	}
	
	/**
	 * Gets the form fields as defined through the metadata
	 * on {@link $obj} and the custom parameters passed to FormScaffolder.
	 * Depending on those parameters, the fields can be used in ajax-context,
	 * contain {@link TabSet}s etc.
	 * 
	 * @return FieldList
	 */
	public function getFieldList() {
		$fields = new FieldList();
		
		// tabbed or untabbed
		if($this->tabbed) {
			$fields->push(new TabSet("Root", $mainTab = new Tab("Main")));
			$mainTab->setTitle(_t('SiteTree.TABMAIN', "Main"));
		}
		//var_dump($this->obj->db());exit();
		// add database fields
		foreach($this->obj->db() as $fieldName => $fieldType) {
			if($this->restrictFields && !in_array($fieldName, $this->restrictFields)) continue;
			
			// @todo Pass localized title
			if($this->fieldClasses && isset($this->fieldClasses[$fieldName])) {
				$fieldClass = $this->fieldClasses[$fieldName];
				$fieldObject = new $fieldClass($fieldName);
			} else {
				$fieldObject = $this->obj->dbObject($fieldName)->scaffoldFormField(null, $this->getParamsArray());
			}
			$fieldObject->setTitle($this->obj->fieldLabel($fieldName));
			if($this->tabbed) {
				$fields->addFieldToTab("Root.Main", $fieldObject);
			} else {
				$fields->push($fieldObject);
			}
		}
		
		return $fields;
	}
	
	/**
	 * Return an array suitable for passing on to {@link DBField->scaffoldFormField()}
	 * without tying this call to a FormScaffolder interface.
	 * 
	 * @return array
	 */
	protected function getParamsArray() {
		return array(
			'tabbed' => $this->tabbed,
			'includeRelations' => $this->includeRelations,
			'restrictFields' => $this->restrictFields,
			'fieldClasses' => $this->fieldClasses,
			'ajaxSafe' => $this->ajaxSafe
		);
	}
}