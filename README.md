adminer-plugin-dump-import-friendly
===================================

Adminer-Plugin: Create import friendly SQL dumps

## Description

This plugin adds an additional export format which outputs all sql statements in one line. Additionally every row is outputted as single insert-statements. The resulting output is easy to import using scripts that slice the file in lines and importing chunks such as bigdump (http://www.ozerov.de/bigdump/).

## Project Structure

/core/* - Adminer core files. Update those with your preferred Adminer version
/plugins/pos-dump-import-friendly.php - the plugin
index.php - script that launches Adminer with the plugin

## Usage

Just copy the repo in your preferred directory and open index.php. Open it, log in and click "export". Choose "SQL import friendly" and export your data.

## Remarks

I do not take any responsibility for lost or invalid data caused by this piece of code. Usage at your own risk!
Please feel free to contribute or tell me about potential improvements.