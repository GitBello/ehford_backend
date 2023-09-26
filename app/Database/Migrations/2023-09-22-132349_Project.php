<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Project extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'project_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'descrition' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
            ],
            'start_date' => [
                'type' => 'Date',
            ],
            'end_date' => [
                'type' => 'Date',
            ],
            'status' => [
                'type' => 'INT',
                'constraint' => '2',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('projects');
    }

    public function down()
    {
        $this->forge->dropTable('projects');
    }
}
