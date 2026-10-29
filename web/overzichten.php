<?php

require_once("includes/includes.php");
require_once(CLASSES_PATH . "table.inc.php");	
	
class clsOverzicht extends clsTableDef
{     
     public function __construct() 
     {
          parent::__construct();
                   
          $vandaag = date("Y-m-d");
          /*toegevoegd n.a.v. ticketnummer [[326]]*/
          $this->selectsql = "SELECT b.bestelling_id AS bestelling_id,/*dit*/ 
                                     b.tafel AS tafelnr,
                                     b.aantal AS aantalitems,
                                     m.menuitemnaam AS naam,
                                     IF(b.gereed = 1,
                                        IF(s.gerechtcode = 'drk', 'Drank klaar', 'Gerecht klaar'), /*dit*/
                                        'Nog niet klaar') AS status,
                                     r.reservering_id AS res_id
                              FROM reservering r
                              LEFT JOIN bestelling b
                                     ON r.reservering_id = b.reservering_id
                                   LEFT JOIN menuitem m 
                                     ON b.menuitemcode = m.menuitemcode
                                   LEFT JOIN subgerecht s 
                                     ON m.subgerechtcode = s.subgerechtcode 
                                   WHERE r.datum = '$vandaag'  
                                     AND s.gerechtcode "; 
          $this->tablename = "reservering";
          $this->key = "res_id";
          $this->setReadOnly(true);
          
          $column = new clsColumn();
          $column->setFieldName("tafelnr");
          $column->setCaption("Tafel");         
          $this->columnHeader->addColumn($column);
          
          $column = new clsColumn();
          $column->setFieldName("aantalitems");
          $column->setCaption("Aantal");         
          $this->columnHeader->addColumn($column);
          
          $column = new clsColumn();
          $column->setFieldName("naam");
          $column->setCaption("Gerecht");
          $this->columnHeader->addColumn($column);

          /*toegevoegd n.a.v. ticketnummer [[326]]*/
          $overzicht = $_GET['overzicht'] ?? 'kok';

          $column = new clsColumn();
          $column->setFieldName("status");
          $column->setCaption("Status");
          $this->columnHeader->addColumn($column);

          if ($overzicht != "ober") {
               $column = new clsColumn();
               $column->setFieldName("");
               $column->setColumnHtml("<a href='?overzicht=$overzicht&gereed=<!bestelling_id!>' class='btn btn-success'>Gereed</a>");
               $this->columnHeader->addColumn($column);
          }
     } 
}

class clsOverzichtKok extends clsOverzicht
{
     public function getSelectSql()
     {// Overwrite
          return $this->selectsql .= "<> 'drk'"; 
     }
}

/*toegevoegd n.a.v. ticketnummer [[330]]*/
class clsOverzichtBarman extends clsOverzicht
{     
     public function getSelectSql()
     { // Overwrite
          return $this->selectsql .= "= 'drk'"; 
     }
}

class clsOverzichtOber extends clsOverzicht
{
     public function getSelectSql()
     {
          return $this->selectsql .= "IS NOT NULL AND b.gereed = 1";
     }
}

class clsPage extends clsDefaultPage
{
     protected function contentHtml()
{
     /*toegevoegd n.a.v. ticketnummer [[326]]*/
     if (isset($_GET['gereed'])) {
          $id = (int) $_GET['gereed'];
          database::connect()->query("UPDATE bestelling SET gereed = 1 WHERE bestelling_id = $id");
     }

     // Gewijzigd n.a.v. ticketnummer [[245]]
     if (isset($_GET['overzicht'])) {
          return $this->getOverzicht($_GET['overzicht']);
     }

     return "";
     // Einde gewijzigd n.a.v. ticketnummer [[245]]
}
     
     protected function getOverzicht($voorWie) 
     {         
          if ($voorWie == "kok") {
               $this->datalist = new clsOverzichtKok();
          }
          elseif ($voorWie == "barman") {
               $this->datalist = new clsOverzichtBarman();
          }
          else {
               $this->datalist = new clsOverzichtOber();
          }
          return $this->datalist->getTableHtml();
     }
}
     
     $page = new clsPage();
	echo $page->getHtml();
	
?>
