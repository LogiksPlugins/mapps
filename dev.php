<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("uitest_forms")) {

	function _service_uitest_ajax() {
		return [
			"A"=>"B",
			"C"=>"D",
		];
	}

	function uitestForms() {
		if(!isset($_REQUEST['ui'])) $_REQUEST['ui'] = "simple";//simple,tabbed,accordion

		$formConfig = [
				"schema"=> "1.0",
				"title"=> "Test Form",
				"category"=> "Test",
				"privilege"=> "*",
				"blocked"=> false,
				"template"=>$_REQUEST['ui'],
				"dbkey"=>"core",
				"hooks"=> [
					"preload"=> [
						"helpers"=> ["countries"]
					]
				],
				"source"=> [
					"type"=> "sql",
					"table"=> "uitest_tbl",
					"where"=> ["md5(id)"]
				],
				"forcefill"=> [
					"guid"=> "#SESS_GUID#"
				],
				"sourcefile"=>dirname(__FILE__)."/test.json",
				"gotolink"=> "infoview/uitest/{hashid}?",
				"fields"=> [
					"field_textfield"=>[
						"label"=> "Simple Text Field",
						"required"=> true,
						"group"=> "Info",
					],
					"field_pattern"=>[
						"label"=> "Pattern Field",
						"pattern"=>"[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$",
						"placeholder"=>"EMail pattern",
						"group"=> "Info",
					],
					"field_dataSelectorFromTable"=> [
						"label"=> "DB Dropdown",
						"group"=> "Dropdown",
						"required"=> true,
						"dbkey"=>"core",
						"type"=> "dataSelectorFromTable",
						"table"=> "lgks_users",
					    "columns"=> "name as title, id as value",
						"where"=>[
						  "blocked"=>"false"  
						],
						"no-option"=>"Select User Name",
						"ajaxchain"=>[
							"target"=>"field_selectAJAX_target",
							"scmd"=>"mapps/uitest_ajax",
							//"src"=>""
						]
					],
					"field_selectAJAX_target"=>[
						"label"=> "AJAX Target",
						"group"=> "Dropdown",
					],
					//autocomplete

					"field_dataSelectorFromTable_multiple"=> [
						"label"=> "DB Dropdown - Multi",
						"group"=> "Dropdown",
						"multiple"=> true,
						"dbkey"=>"core",
						"type"=> "dataSelectorFromTable",
						"table"=> "lgks_users",
					    "columns"=> "name as title, id as value",
						"where"=>[
						  "blocked"=>"false"  
						],
						"no-option"=>"Select User Name"
					],
					"field_dataSelectorFromTable_search"=> [
						"label"=> "Search Dropdown",
						"group"=> "Dropdown",
						"search"=> true,
						"dbkey"=>"core",
						"type"=> "dataSelectorFromTable",
						"table"=> "lgks_users",
					    "columns"=> "name as title, id as value",
						"where"=>[
						  "blocked"=>"false"  
						],
						"no-option"=>"Select User Name"
					],
					"field_selectAJAX"=> [
						"label"=> "AJAX Dropdown",
						"group"=> "Dropdown",
						"dbkey"=>"core",
						"type"=> "selectAJAX",
						"table"=> "lgks_users",
					    "columns"=> "name as title, id as value",
						"where"=>[
						  "blocked"=>"false"  
						],
						"no-option"=>"Select User Name"
					],

					"field_autosuggest"=> [
						"label"=> "Autosuggest Dropdown",
						"group"=> "Info",
						"type"=> "autosuggest",
						"source"=> [
							"table"=> "profiletbl",
							"where"=>[
							  "blocked"=>"false"  
							],
						],
						"no-option"=>"Select User Name"
					],
				]
			];
		$listElement = [
			"select"=>[
				"options"=> [
					"A"=>"A1",
					"B"=>"B1",
					"C"=>"C1",
					"D"=>"D1",
					"E"=>"E1",
				]
			],
			"radiolist"=>[
				"options"=> [
					"A"=>"A1",
					"B"=>"B1",
					"C"=>"C1",
					"D"=>"D1",
					"E"=>"E1",
				]
			],
			"textarea"=>[],
			"richtextarea"=>[],
			"markup"=>[],
			"color"=>[],
			"checkbox"=>[],
			"date"=>[], 
			"datetime"=>[], 
			"month"=>[], 
			"year"=>[], 
			"time"=>[],
			"currency"=>[],
			"creditcard"=>[],
			"debitcard"=>[],
			"moneycard"=>[],
			"email"=>[],
			"tel"=>[],
			"phone"=>[],
			"mobile"=>[],
			"url"=>[],
			"number"=>[],
			"barcode"=>[],
			"qrcode"=>[],
			"search"=>[],
			"password"=>[],
			"tags"=>[],
			"social@facebook"=>[],
			"static"=>["placeholder"=>"Hello World"],

			"jsonfield"=>[

			],

			"photo"=>[],
			"photo-multiple"=>["type"=>"photo","multiple"=>true],
			"gallery"=>[],
			"gallery-multiple"=>["type"=>"gallery","multiple"=>true],
			"avatar"=>[],
			"file"=>[],
			"file-multiple"=>["type"=>"file","multiple"=>true],
		];

		foreach($listElement as $typeKey=>$default) {
			if(!isset($default['type'])) $default['type'] = $typeKey;

			$formConfig['fields']["field_{$typeKey}"] = array_merge([
					"label"=>$typeKey,
					"group"=>"Info",
					"type"=>$default['type'],
				],$default);
		}

		//echo json_encode($formConfig);exit();
		return $formConfig;
	}

	function uitestReportsData() {
		return [[
				"hashid"=> "c4ca4238a0b923820dcc509a6f75849b",
				"reportid"=> "uitest",
				"title"=> "UI Test 1",
				"image"=> loadMedia("images/user.png"),
				"descs"=> "UI Testing descs and lot of content is here then there is not limit to visual content shown here",
				"msg"=> "UI Testing message",
				"due_date"=> "04-18-2020",
				"category"=> "General",
				"tags"=> "newTag",
				"flag"=> "flag1",
				"color"=> "red",
				"link_url"=> "#",
				"buttons"=> [
					"infoview@uitest/c4ca4238a0b923820dcc509a6f75849b"=> [
						"label"=> "View Visit",
						"icon"=> "fa fa-eye"
					],
					"forms@uitest/edit/c4ca4238a0b923820dcc509a6f75849b"=> [
						"label"=> "Edit Visit",
						"icon"=> "fa fa-pencil",
						"color"=> "red"
					]
				],
				"actions"=> [
					"forms@uitest/new"=> [
						"label"=> "Add Visit",
						"icon"=> "fa fa-plus",
						"class"=> "btn btn-info product"
					]
				],

			]];
	}

	function uitestReports() {
		$reportConfig = [
					"schema"=> "1.0",
					"title"=> "UITest Report",
					"category"=> "UITesting",
					"privilege"=> "*",
					"blocked"=> false,
					"rowlink"=> false,
					"rowsPerPage"=> 20,
					"showExtraColumn"=> false,
					"custombar"=> false,
					"dbkey"=>"core",
					"source"=> [
						"type"=> "sql",
						"table"=> "lgks_users",
						"cols"=> "id",//,name,email,city,state
						"limit"=> 20
					],
					"actions"=> [
						"forms@uitest/new"=> [
							"label"=> "Add Risk",
							"icon"=> "fa fa-plus",
							"class"=> "btn btn-info risk"
						]
					],
					"buttons"=> [
						"infoview@uitest/123213123"=> [
							"icon"=> "fa fa-eye",
							"class"=> "risk",
							"label"=> "View Risk"
						],
						"forms@uitest/edit/123213123"=> [
							"icon"=> "fa fa-pencil",
							"class"=> "risk",
							"label"=> "Edit Risk"
						]
					],
					"datagrid"=> [
						"id"=> [
							"label"=> "ID",
							"hidden"=> false,
							"searchable"=> true,
							"sortable"=> true,
							"groupable"=> false,
							"classes"=> "",
							"style"=> "width=>50px;",
							"formatter"=> "text"
						],
						"name"=> [
							"label"=> "Name",
							"sortable"=> true,
							"searchable"=> true
						],
						"city"=> [
							"label"=> "City",
							"sortable"=> true,
							"searchable"=> true
						],
						"state"=> [
							"label"=> "State",
							"sortable"=> true,
							"searchable"=> true
						],
						"risk_level"=> [
							"label"=> "Level",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"risk_priority"=> [
							"label"=> "Priority",
							"sortable"=> true,
							"searchable"=> true
						],
						"risk_category"=> [
							"label"=> "Category",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"risk_tags"=> [
							"label"=> "Tags",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"risk_flag"=> [
							"label"=> "Flag",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"risk_on"=> [
							"label"=> "On",
							"formatter"=>"date",
							"sortable"=> true,
							"searchable"=> true
						],
						"risk_status"=> [
							"label"=> "Status",
							"sortable"=> true,
							"searchable"=> true
						],
						"verified"=> [
							"label"=> "Verified",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"verified_by"=> [
							"label"=> "Verified By",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"verified_on"=> [
							"label"=> "Verified On",
							"formatter"=> "date",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"resolved"=> [
							"label"=> " Resolved",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"resolved_by"=> [
							"label"=> " Resolved By",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"resolved_on"=> [
							"label"=> " Resolved On",
							"formatter"=> "date",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						],
						"blocked"=> [
							"label"=> "Blocked",
							"sortable"=> true,
							"searchable"=> true,
							"hidden"=> true
						]
					],
					"kanban"=>[
						"colkeys"=>[
							"name"=>[
								"label"=> "User Name",
								"table"=> "lgks_users",
								"columns"=> "name as title,id as value"
							]
						],
						"colmap"=>[
							"title"=>"name",
							"descs"=>"state"
						],
						"unilink"=>"infoview@uitest"
					],
					"cards"=>[
						"colkeys"=>[
							"name"=>[
								"label"=> "User Name",
								"table"=> "lgks_users",
								"columns"=> "name as title,id as value"
							]
						],
						"colmap"=>[
							"title"=>"name",
							"descs"=>"state"
						],
						"unilink"=>"infoview@uitest"
					]
				];

		return $reportConfig;
	}

	function uitestInfoview() {
		if(!isset($_REQUEST['ui'])) $_REQUEST['ui'] = "tabbed";//simple,tabbed,accordion
		
		$formConfig = uitestForms();

		$formConfig['source']['type'] = "demo";
		$formConfig['srckey'] = "demo";

		$formConfig['infoview'] = [
			//"template"=>"accordion",
			"groups"=>[
				"tab0"=> [
					"label"=> "Tab Single",
					"type"=> "module",
					"src"=> "infoviewTable",
					"vmode"=> "view",
					"dbKey"=>"core",
					"dbkey"=>"core",
					"config"=> [
						"dbKey"=>"core",
						"dbkey"=>"core",
						"type"=> "sql",
						"uimode"=> "single",
						"table"=> "lgks_users",
						"cols"=> "id,name,state",
						"where"=> "id>0"
					],
					"width"=> 12
				],
				"tab1"=>[
				    "label"=>"Tab Grid",
				    "type"=>"module",
				    "src"=>"infoviewTable",
				    "vmode"=>"view",
				    "config"=>[
				    	"dbkey"=>"core",
				        "type"=>"sql",
				        "uimode"=>"grid",
				        "table"=>"lgks_users",
				        "cols"=>"lgks_users.id,lgks_users.name,lgks_users.state",
				        "where"=>"lgks_users.id<10"
				    ],
				    "width"=>12
				],
				"files"=> [
					"label"=> "Files",
					"type"=> "module",
					"src"=> "docman.docs",
					"vmode"=> "edit",
					"config"=> [
					    "ref_id"=>"#refid#",
					    "ref_src"=>"Xtest"
					],
					"width"=> 12
				],
				"comments"=> [
					"label"=> "Comments",
					"type"=> "module",
					"src"=> "userComments.comments",
					"config"=> [
					    "ref_id"=>"#refid#",
					     "ref_src"=>"Xtest"
					],
					"vmode"=> "edit",
					"width"=> 12,
					"hidden"=> true
				],
				"notes"=> [
					"label"=> "Notes",
					"type"=> "module",
					"src"=> "notesBoard.notes",
					"rule"=> "profile,#refid#",
					"vmode"=> "view",
					"width"=> 12,
					"hidden"=> true
				],
				"logs"=> [
					"label"=> "Logs",
					"type"=> "module",
					"src"=> "bizlogger.logs",
					"rule"=> "profile,#refid#",
					"vmode"=> "view",
					"width"=> 12,
					"config"=> [
	    				"ref_id"=>"#refid#",
	    				"ref_src"=>"Xtest"
	    			],
					"hidden"=> true
				],
				"extras"=> [
					"label"=> "Extra",
					"type"=> "widget",
					"src"=> "test_chart",
					"vmode"=> "view",
					"width"=> 12
				]
			]
		];

		return $formConfig;
	}

	function uitestViews() {
		return false;
	}

	function uitestGrids() {
		return false;
	}

	function uitestInfovisual() {
		return false;
	}

	function uitestSearch() {
		return false;
	}

	function uitestPanel() {
		return "UI Test Panel";
	}

	function uitestComponent() {
		return "Test Component";
	}
}	
?>