<?php

$this->startSetup();

$this->run("

		ALTER TABLE  {$this->getTable('configurator/template')} ADD listimage_style int(11) NULL;
		ALTER TABLE  {$this->getTable('configurator/template')} ADD listimage_items_per_line int(11) NULL DEFAULT 5;
		UPDATE {$this->getTable('configurator/template')} SET listimage_items_per_line = 5;
");

$this->endSetup();