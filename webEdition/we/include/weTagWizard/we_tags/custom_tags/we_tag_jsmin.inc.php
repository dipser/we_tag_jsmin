<?php

$this->NeedsEndTag = false;
$this->Description = "Mit we:jsmin werden Javascripte in einer Zieldokument gespeichert.";

if(defined("FILE_TABLE")) { 
	$this->Attributes[] = new weTagData_selectorAttribute('src', FILE_TABLE, 'application/*', true, '');
	$this->Attributes[] = new weTagData_selectorAttribute('target', FILE_TABLE, 'application/*', true, '');
}

$this->Attributes[] = new weTagData_selectAttribute('watch', array(new weTagDataOption('true', false, ''), new weTagDataOption('false', false, '')), false,'');
$this->Attributes[] = new weTagData_selectAttribute('forceUpdate', array(new weTagDataOption('true', false, ''), new weTagDataOption('false', false, '')), false,''); 
$this->Attributes[] = new weTagData_selectAttribute('only', array(new weTagDataOption('href', false, '')), false, '');
