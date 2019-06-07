<?php

$this->startSetup();

$this->run("

		ALTER TABLE {$this->getTable('configurator/option_value_blacklist')} ADD child_option_id int(11) NULL;
		ALTER TABLE {$this->getTable('configurator/option_value_blacklist')} MODIFY child_option_value_id int(11) NULL;
		ALTER TABLE {$this->getTable('configurator/option_value_blacklist')} ADD FOREIGN KEY ( `child_option_id` ) REFERENCES {$this->getTable('configurator/option')} (
			`id`
		) ON DELETE CASCADE ON UPDATE CASCADE;
		
		ALTER TABLE {$this->getTable('configurator/option_blacklist')} ADD FOREIGN KEY ( `option_id` ) REFERENCES {$this->getTable('configurator/option')} (
			`id`
		) ON DELETE CASCADE ON UPDATE CASCADE;
		ALTER TABLE {$this->getTable('configurator/option_blacklist')} ADD FOREIGN KEY ( `child_option_value_id` ) REFERENCES {$this->getTable('configurator/option_value')} (
			`id`
		) ON DELETE CASCADE ON UPDATE CASCADE;
		
		ALTER TABLE {$this->getTable('configurator/option_valuetag_blacklist')} ADD FOREIGN KEY ( `option_value_id` ) REFERENCES {$this->getTable('configurator/option_value')} (
			`id`
		) ON DELETE CASCADE ON UPDATE CASCADE;
		ALTER TABLE {$this->getTable('configurator/option_valuetag_blacklist')} ADD FOREIGN KEY ( `option_id` ) REFERENCES {$this->getTable('configurator/option')} (
			`id`
		) ON DELETE CASCADE ON UPDATE CASCADE;
		ALTER TABLE {$this->getTable('configurator/option_valuetag_blacklist')} ADD FOREIGN KEY ( `related_option_id` ) REFERENCES {$this->getTable('configurator/option')} (
			`id`
		) ON DELETE CASCADE ON UPDATE CASCADE;
		
		ALTER TABLE {$this->getTable('configurator/option_valuetag')} ADD UNIQUE ( `option_value_id`, `tag`);
		ALTER TABLE {$this->getTable('configurator/option_valuetag')} ADD FOREIGN KEY ( `option_value_id` ) REFERENCES {$this->getTable('configurator/option_value')} (
			`id`
		) ON DELETE CASCADE ON UPDATE CASCADE;
		
");

$this->endSetup();