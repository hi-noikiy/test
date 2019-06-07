<?php
$this->startSetup();

$this->run("

		ALTER TABLE  {$this->getTable('configurator/template')} ADD listimage_mouseover int(11) NOT NULL DEFAULT 0;
		UPDATE {$this->getTable('configurator/template')} SET listimage_mouseover = 0;
");

$this->endSetup();