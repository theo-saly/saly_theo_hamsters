<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251122213100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE maintenance (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, interval_km INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE maintenance_maintenance_task (maintenance_id INT NOT NULL, maintenance_task_id INT NOT NULL, INDEX IDX_33F04E32F6C202BC (maintenance_id), INDEX IDX_33F04E32F4404414 (maintenance_task_id), PRIMARY KEY (maintenance_id, maintenance_task_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE maintenance_task (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, cost NUMERIC(10, 0) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, scheduled_at DATETIME DEFAULT NULL, is_sent TINYINT(1) NOT NULL, user_id INT DEFAULT NULL, vehicle_id INT DEFAULT NULL, maintenance_id INT DEFAULT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), INDEX IDX_BF5476CA545317D1 (vehicle_id), INDEX IDX_BF5476CAF6C202BC (maintenance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(100) DEFAULT NULL, lastname VARCHAR(100) DEFAULT NULL, created_at DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicle (id INT AUTO_INCREMENT NOT NULL, plate_number VARCHAR(20) NOT NULL, vin VARCHAR(50) NOT NULL, brand VARCHAR(50) DEFAULT NULL, model VARCHAR(50) DEFAULT NULL, year INT DEFAULT NULL, mileage INT DEFAULT NULL, fuel_type VARCHAR(50) DEFAULT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, user_id INT NOT NULL, INDEX IDX_1B80E486A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE vehicle_maintenance (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT DEFAULT NULL, maintenance_id INT DEFAULT NULL, INDEX IDX_A1BF859F545317D1 (vehicle_id), INDEX IDX_A1BF859FF6C202BC (maintenance_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE maintenance_maintenance_task ADD CONSTRAINT FK_33F04E32F6C202BC FOREIGN KEY (maintenance_id) REFERENCES maintenance (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE maintenance_maintenance_task ADD CONSTRAINT FK_33F04E32F4404414 FOREIGN KEY (maintenance_task_id) REFERENCES maintenance_task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAF6C202BC FOREIGN KEY (maintenance_id) REFERENCES maintenance (id)');
        $this->addSql('ALTER TABLE vehicle ADD CONSTRAINT FK_1B80E486A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vehicle_maintenance ADD CONSTRAINT FK_A1BF859F545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicle (id)');
        $this->addSql('ALTER TABLE vehicle_maintenance ADD CONSTRAINT FK_A1BF859FF6C202BC FOREIGN KEY (maintenance_id) REFERENCES maintenance (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE maintenance_maintenance_task DROP FOREIGN KEY FK_33F04E32F6C202BC');
        $this->addSql('ALTER TABLE maintenance_maintenance_task DROP FOREIGN KEY FK_33F04E32F4404414');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA545317D1');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAF6C202BC');
        $this->addSql('ALTER TABLE vehicle DROP FOREIGN KEY FK_1B80E486A76ED395');
        $this->addSql('ALTER TABLE vehicle_maintenance DROP FOREIGN KEY FK_A1BF859F545317D1');
        $this->addSql('ALTER TABLE vehicle_maintenance DROP FOREIGN KEY FK_A1BF859FF6C202BC');
        $this->addSql('DROP TABLE maintenance');
        $this->addSql('DROP TABLE maintenance_maintenance_task');
        $this->addSql('DROP TABLE maintenance_task');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE vehicle');
        $this->addSql('DROP TABLE vehicle_maintenance');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
