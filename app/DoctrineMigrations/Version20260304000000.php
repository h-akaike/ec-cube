<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260304000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'チケット残高・消化履歴テーブルを作成';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('dtb_ticket_balance');
        $table->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table->addColumn('customer_id', 'integer', ['unsigned' => true]);
        $table->addColumn('order_id', 'integer', ['unsigned' => true, 'notnull' => false]);
        $table->addColumn('total_hours', 'decimal', ['precision' => 10, 'scale' => 1]);
        $table->addColumn('used_hours', 'decimal', ['precision' => 10, 'scale' => 1, 'default' => 0]);
        $table->addColumn('expires_at', 'datetimetz');
        $table->addColumn('create_date', 'datetimetz');
        $table->addColumn('update_date', 'datetimetz');
        $table->addColumn('discriminator_type', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['customer_id'], 'idx_ticket_balance_customer');
        $table->addForeignKeyConstraint('dtb_customer', ['customer_id'], ['id']);
        $table->addForeignKeyConstraint('dtb_order', ['order_id'], ['id']);

        $table2 = $schema->createTable('dtb_ticket_usage');
        $table2->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $table2->addColumn('ticket_balance_id', 'integer', ['unsigned' => true]);
        $table2->addColumn('customer_id', 'integer', ['unsigned' => true]);
        $table2->addColumn('hours', 'decimal', ['precision' => 10, 'scale' => 1]);
        $table2->addColumn('service_type', 'string', ['length' => 50]);
        $table2->addColumn('description', 'text', ['notnull' => false]);
        $table2->addColumn('work_date', 'date');
        $table2->addColumn('staff_name', 'string', ['length' => 100]);
        $table2->addColumn('create_date', 'datetimetz');
        $table2->addColumn('discriminator_type', 'string', ['length' => 255]);
        $table2->setPrimaryKey(['id']);
        $table2->addIndex(['customer_id'], 'idx_ticket_usage_customer');
        $table2->addIndex(['ticket_balance_id'], 'idx_ticket_usage_balance');
        $table2->addForeignKeyConstraint('dtb_ticket_balance', ['ticket_balance_id'], ['id']);
        $table2->addForeignKeyConstraint('dtb_customer', ['customer_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('dtb_ticket_usage');
        $schema->dropTable('dtb_ticket_balance');
    }
}
