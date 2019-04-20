<?php

// Query Definitions

class QueryDefs {

    protected $version = "SELECT `value` FROM setting WHERE varname='templateversion'";

    protected $modules = [
            'categories'      => "SELECT distinct(category) FROM widget WHERE category != 'Abstract';",
            'widget'          => "SELECT * FROM widget WHERE template!='' AND isthirdparty=0 AND category = ?;",
            'templatephrase'  => "SELECT p.text FROM widget AS w 
                                  LEFT JOIN phrase AS p ON (p.varname = concat(w.template,'_widgettitle')) 
                                  WHERE w.widgetid=?;",
            'titlephrase'     => "SELECT p.text FROM widget AS w 
                                  LEFT JOIN phrase AS p ON (p.varname = w.titlephrase) 
                                  WHERE w.widgetid=?;",
            'definitions'     => "",
    ];

    protected $options = [
            'groups'          => "SELECT sg.*, p.text AS title FROM settinggroup AS sg LEFT JOIN phrase AS p ON (p.varname LIKE CONCAT('settinggroup_',sg.grouptitle)) WHERE sg.product='vbulletin' ORDER BY sg.displayorder",
            'settings'        => "SELECT p.text AS 'title', p2.text AS 'description', s.varname, s.defaultvalue, s.datatype, s.displayorder FROM setting AS s 
                                  LEFT JOIN settinggroup AS sg ON (s.grouptitle = sg.grouptitle) 
                                  LEFT JOIN phrase AS p ON (p.varname LIKE CONCAT('setting_', s.varname, '_title')) 
                                  LEFT JOIN phrase AS p2 ON (p2.varname LIKE CONCAT('setting_', s.varname, '_desc')) 
                                  WHERE s.grouptitle=? ORDER BY s.displayorder",
    ];

    protected $pages = [];

    protected $permissions = [];


    protected $stylevars = [
            'groups'          => "SELECT DISTINCT(stylevargroup) FROM stylevardfn ORDER BY stylevargroup ASC;",
            'stylevars'       => "SELECT p1.text AS 'title', p2.text AS 'description', s.* FROM stylevardfn AS s
                                  LEFT JOIN phrase AS p1 ON (p1.varname LIKE CONCAT('stylevar_', s.stylevarid, '_name')) 
                                  LEFT JOIN phrase AS p2 ON (p2.varname LIKE CONCAT('stylevar_', s.stylevarid, '_description')) 
                                  WHERE stylevargroup=? ORDER BY s.varname ASC", 
            'default_value'   => "SELECT value FROM stylevar WHERE stylevarid=? AND styleid=-1",
    ];

        public function getQueries($property) 
    {
        if (property_exists($this, $property)) {
          return $this->$property;
        }
    }

    public function getVersion($db)
    {
        return $db->run_query($this->version)->fetchColumn();
    }
}