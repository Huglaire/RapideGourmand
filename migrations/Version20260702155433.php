<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260702155433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `order` (id INT AUTO_INCREMENT NOT NULL, order_date DATETIME NOT NULL, delivery_date DATETIME NOT NULL, guest_number INT NOT NULL, delivery_street VARCHAR(255) NOT NULL, delivery_postal_code VARCHAR(32) NOT NULL, delivery_city VARCHAR(64) NOT NULL, delivery_fee NUMERIC(5, 2) NOT NULL, total_price NUMERIC(8, 2) NOT NULL, equipment_borrowed TINYINT NOT NULL, status VARCHAR(50) NOT NULL, cancel_contact_method VARCHAR(50) DEFAULT NULL, cancel_reason LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, cancel_date DATETIME DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `order`');
    }
}
