<?php
/**
 * @copyright Copyright (c) PutYourLightsOn
 */

namespace putyourlightson\sprig\plugin\migrations;

use craft\db\Migration;
use putyourlightson\sprig\plugin\records\PlaygroundRecord;

class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        if (!$this->db->tableExists(PlaygroundRecord::tableName())) {
            $this->createTable(PlaygroundRecord::tableName(), [
                'id' => $this->primaryKey(),
                'name' => $this->string(),
                'component' => $this->text(),
                'variables' => $this->text(),
                'dateCreated' => $this->dateTime()->notNull(),
                'dateUpdated' => $this->dateTime()->notNull(),
                'uid' => $this->uid(),
            ]);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(PlaygroundRecord::tableName());

        return true;
    }
}
