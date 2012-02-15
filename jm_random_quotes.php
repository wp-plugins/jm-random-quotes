<?php
	/*
	Plugin Name: JM Random Quotes
	Plugin URI: http://gplit.com
	Description: This plugin will randomly choose a quote from a database of quotes that you add to it.
	Version: 1.1.1
	Author: Josten Moore
	Author URI: http://gplit.com
	License: GPL2
	*/
	
	/*  Copyright 2011  JM Random Quotes  (http://gplit.com/about/contact/)

		This program is free software; you can redistribute it and/or modify
		it under the terms of the GNU General Public License, version 2, as 
		published by the Free Software Foundation.

		This program is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with this program; if not, write to the Free Software
		Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/
	
	$jmrq_title = "JM Random Quotes";
	$jmrq_title_stripped = str_replace(" ", "-", $jmrq_title);
	
	//The widget display name
	$jmrq_widget_name = "Random Quotes";
	
	//jmrq_table_name is a global variable the for the name of the table that the quotes are stored in
	define("jmrq_table_name", "$wpdb->prefix" . "jmrq_random_quotes");
	
	class Widget_JM_Random_Quotes extends WP_Widget {
		function Widget_JM_Random_Quotes() {
			global $jmrq_title;
			global $jmrq_title_stripped;
		
			$widget_options = array(
				'classname' => $jmrq_title_stripped,
				'description' => __("$jmrq_title")
			);
			
			$control_options = array(
				'height' => 400,
				'width' => 400
			);
			
			$this->WP_Widget("JM-Random-Quotes", __("$jmrq_title"), $widget_options, $control_options);
		}
		
		function widget($args, $instance) {
			global $wpdb;
			global $jmrq_widget_name;
			
			$sql = "SELECT * FROM " . jmrq_table_name;
			$tableCount = $wpdb->query($sql);
			$randNum = rand(0, $tableCount -1);
			
			$quote = $wpdb->get_var($sql, 0, $randNum);
			$author = $wpdb->get_var($sql, 1, $randNum);
			
			echo "<h3 class=\"widget-title\">" . $jmrq_widget_name . "</h3>";
			echo "<i id=\"random_quote\">$quote</i>";
			echo "<p id=\"quote_author\"><br />-$author</p>";
		}
		
		function form($instance) {
			_e("All configuration is done in Settings > JM Random Quotes");
		}
		
		function update($new_instance, $old_instance) {
		}
	}
	
	//Form submission
	if(isset($_POST["quote"]) && isset($_POST["author"]))
	{
		global $wpdb;
		$quote = $_POST["quote"];
		$author = $_POST["author"];
		
		//As long as the quote and author length is over 1 add the quote and author to the table
		if(strlen($_POST["quote"]) > 1 && strlen($_POST["author"]) > 1)
		{
			$wpdb->insert(jmrq_table_name, array("quote" => $quote, "author" => $author));
		}
	}
	
	//Quote deletion
	if(isset($_POST["quote_deletion"]) && isset($_POST["radio_quotes"]))
	{
		global $wpdb;
		
		//When form is submitted; the specified row will be stored in this variable
		$row_to_delete = $_POST["radio_quotes"];
		
		//Get everything from the quotes table
		$sql1 = "SELECT * FROM " . jmrq_table_name;
		
		//Use the 
		$quote = $wpdb->get_var($sql1, 0, $row_to_delete);
		$author = $wpdb->get_var($sql1, 1, $row_to_delete);
		
		//Deletion of specified row
		$wpdb->query(	"DELETE FROM " . jmrq_table_name . 
						" WHERE quote=\"" . $wpdb->get_var($sql1, 0, $row_to_delete) . "\" AND" .
						" author=\"" . $wpdb->get_var($sql1, 1, $row_to_delete) . "\"");
	}

	function jmrq_init_config()
	{
		add_options_page("JM Random Quotes", "JM Random Quotes", "manage_options", "random_quotes", "jmrq_create_admin_form");
		jmrq_create_table();
	}
	
	function jmrq_create_admin_form()
	{
		submission_form();
	}
	
	function submission_form()
	{
		//Quote submission
		echo "<h3>Add a quote to the database</h3>";
		echo "<form action=\"\" method=\"post\">";
			echo "Quote: <input type=\"text\" name=\"quote\" size=\"80\" /> Author: <input type=\"text\" name=\"author\" />";
			echo "<input type=\"submit\" name=\"submit_button\" value=\"Add Quote\" />";
		echo "</form>";
		
		//Quote retrieval (for deletion)
		echo "<br /><br />";
		echo "<h3>Quotes in database</h3>";
		
		$quotes = jmrq_get_quotes();
	
		if($quotes != NULL)
		{
			echo "<form action=\"\" method=\"post\">";
			echo "<table class=\"quote_deletion\" width=\"90%\">";
			echo "<tr>";
				echo "<td><h4>Quote</h4></td>";
				echo "<td><h4>Author</h4></td>";
			echo "</tr>";
			
			for($i = 0; $i < sizeof($quotes); $i++)
			{
				$color = "white";
				if(($i % 2) == 0) { $color = "LightGrey"; }
				
				$quote = $quotes[$i][0];
				$author = $quotes[$i][1];
				
					echo "<tr style=\"background-color: $color\">";
						echo "<td width=\"80%\">$quote</td>";
						echo "<td width=\"15%\">$author</td>";
						echo "<td width=\"5%\"><input type=\"radio\" name=\"radio_quotes\" value=\"$i\"/></td>";
					echo "</tr>";
				
			}
			echo "</table>";
			echo "<input type=\"submit\" name=\"quote_deletion\" value=\"Delete quote\"/>";
			echo "</form>";
		}
	}
	
	//Retrieves the quotes from the database (if any)
	function jmrq_get_quotes()
	{
		global $wpdb;
		$sql = "SELECT * FROM " . jmrq_table_name;
		
		//Stores the total amount of quotes in the database
		$rowCount = $wpdb->query($sql);
		
		$quotes = array(array());
		
		if($rowCount > 0)
		{
			for($i = 0; $i < $rowCount; $i++)
			{
				$quote = $wpdb->get_var($sql, 0, $i);
				$author = $wpdb->get_var($sql, 1, $i);
				
				$quotes[$i][] = $quote;
				$quotes[$i][] = $author;
			}
			
			return $quotes;
		} else if($rowCount <= 0)
		{
			return NULL;
		}
	}
	
	//Create the table if it doesn't exist already
	function jmrq_create_table()
	{
		global $wpdb;
		
		$sql = 	"CREATE TABLE IF NOT EXISTS " . jmrq_table_name . "(
				quote varchar(1000) NOT NULL,
				author varchar(75) NOT NULL
				);";
		
		$wpdb->query($sql);
	}
	
	function jmrq_init_widget() {
		register_widget("Widget_JM_Random_Quotes");
	}
	
	//Register widget
	add_action("widgets_init", "jmrq_init_widget");
	
	//Register config page
	add_action("admin_menu", "jmrq_init_config");
?>