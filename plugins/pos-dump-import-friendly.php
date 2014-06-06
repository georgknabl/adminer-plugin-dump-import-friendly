<?php

/** Creates import friendly dump
* @author Georg Knabl, http://www.pageonstage.at/
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other), I do not take any responsibility for lost or invalid data caused by this piece of code. Usage at your own risk!
*/
class AdminerPosDumpImportFriendly {
	
	function dumpFormat() {
		if (DRIVER == 'server') {
			return array('sql_import_friendly' => 'SQL import friendly');
		}
	}	
	
	function dumpTable($table, $style, $is_view = 0) {
		if ($_POST["format"] != "sql_import_friendly") {
			echo "\xef\xbb\xbf"; // UTF-8 byte order mark
			if ($style) {
				dump_csv(array_keys(fields($table)));
			}
		} elseif ($style) {
			if ($is_view == 2) {
				$fields = array();
				foreach (fields($table) as $name => $field) {
					$fields[] = idf_escape($name) . " $field[full_type]";
				}
				$create = "CREATE TABLE " . table($table) . " (" . implode(", ", $fields) . ")";
			} else {
				$create = create_sql($table, $_POST["auto_increment"]);
			}
			if ($create) {
				if ($style == "DROP+CREATE" || $is_view == 1) {
					echo "DROP " . ($is_view == 2 ? "VIEW" : "TABLE") . " IF EXISTS " . table($table) . ";\n";
				}
				if ($is_view == 1) {
					$create = remove_definer($create);
				}
				$create = str_replace("\n", "\t", $create);
				echo "$create;\n\n";
			}
		}
		
		return true;
	}
	
	function dumpData($table, $style, $query) {
		if ($_POST["format"] != "sql_import_friendly") return;
		
		global $connection, $jush;
		$connection = connection();
		//$max_packet = ($jush == "sqlite" ? 0 : 1048576); // default, minimum is 1024
		$max_packet = 0;
		if ($style) {
			if (true) {
				if ($style == "TRUNCATE+INSERT") {
					echo truncate_sql($table) . ";\n";
				}
				$fields = fields($table);
			}
			$result = $connection->query($query, 1); // 1 - MYSQLI_USE_RESULT //! enum and set as numbers
			if ($result) {
				$insert = "";
				$buffer = "";
				$keys = array();
				$suffix = "";
				$fetch_function = ($table != '' ? 'fetch_assoc' : 'fetch_row');
				while ($row = $result->$fetch_function()) {
					if (!$keys) {
						$values = array();
						foreach ($row as $val) {
							$field = $result->fetch_field();
							$keys[] = $field->name;
							$key = idf_escape($field->name);
							$values[] = "$key = VALUES($key)";
						}
						$suffix = ($style == "INSERT+UPDATE" ? "\nON DUPLICATE KEY UPDATE " . implode(", ", $values) : "") . ";\n";
					}
					if (false) {
						if ($style == "table") {
							dump_csv($keys);
							$style = "INSERT";
						}
						dump_csv($row);
					} else {
						if (!$insert) {
							$insert = "INSERT INTO " . table($table) . " (" . implode(", ", array_map('idf_escape', $keys)) . ") VALUES";
						}
						foreach ($row as $key => $val) {
							$field = $fields[$key];
							$row[$key] = ($val !== null
								? unconvert_field($field, preg_match('~(^|[^o])int|float|double|decimal~', $field["type"]) && $val != '' ? $val : q($val))
								: "NULL"
							);
						}
						$s = ($max_packet ? "\t" : " ") . "(" . implode(",\t", $row) . ")";
						if (!$buffer) {
							$buffer = $insert . $s;
						} elseif (strlen($buffer) + 4 + strlen($s) + strlen($suffix) < $max_packet) { // 4 - length specification
							$buffer .= ",$s";
						} else {
							echo $buffer . $suffix;
							$buffer = $insert . $s;
						}
					}
				}
				if ($buffer) {
					echo $buffer . $suffix;
				}
			} elseif (true) {
				echo "-- " . str_replace("\n", " ", $connection->error) . "\n";
			}
		}
		
		return true;
	}
}
