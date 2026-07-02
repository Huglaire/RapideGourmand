<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260702172337 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE dish_picture (dish_id INT NOT NULL, picture_id INT NOT NULL, INDEX IDX_866C551F148EB0CB (dish_id), INDEX IDX_866C551FEE45BDBF (picture_id), PRIMARY KEY (dish_id, picture_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE dish_allergen (dish_id INT NOT NULL, allergen_id INT NOT NULL, INDEX IDX_3C4389A5148EB0CB (dish_id), INDEX IDX_3C4389A56E775A4A (allergen_id), PRIMARY KEY (dish_id, allergen_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu_theme (menu_id INT NOT NULL, theme_id INT NOT NULL, INDEX IDX_6D9C46FCCD7E912 (menu_id), INDEX IDX_6D9C46F59027487 (theme_id), PRIMARY KEY (menu_id, theme_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu_diet (menu_id INT NOT NULL, diet_id INT NOT NULL, INDEX IDX_55AB956ECCD7E912 (menu_id), INDEX IDX_55AB956EE1E13ACE (diet_id), PRIMARY KEY (menu_id, diet_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu_dish (menu_id INT NOT NULL, dish_id INT NOT NULL, INDEX IDX_5D327CF6CCD7E912 (menu_id), INDEX IDX_5D327CF6148EB0CB (dish_id), PRIMARY KEY (menu_id, dish_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE menu_picture (menu_id INT NOT NULL, picture_id INT NOT NULL, INDEX IDX_9E005237CCD7E912 (menu_id), INDEX IDX_9E005237EE45BDBF (picture_id), PRIMARY KEY (menu_id, picture_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE order_menu (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, customer_order_id INT NOT NULL, menu_id INT NOT NULL, INDEX IDX_30F40084A15A2E17 (customer_order_id), INDEX IDX_30F40084CCD7E912 (menu_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE dish_picture ADD CONSTRAINT FK_866C551F148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish_picture ADD CONSTRAINT FK_866C551FEE45BDBF FOREIGN KEY (picture_id) REFERENCES picture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish_allergen ADD CONSTRAINT FK_3C4389A5148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dish_allergen ADD CONSTRAINT FK_3C4389A56E775A4A FOREIGN KEY (allergen_id) REFERENCES allergen (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_theme ADD CONSTRAINT FK_6D9C46FCCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_theme ADD CONSTRAINT FK_6D9C46F59027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_diet ADD CONSTRAINT FK_55AB956ECCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_diet ADD CONSTRAINT FK_55AB956EE1E13ACE FOREIGN KEY (diet_id) REFERENCES diet (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_dish ADD CONSTRAINT FK_5D327CF6CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_dish ADD CONSTRAINT FK_5D327CF6148EB0CB FOREIGN KEY (dish_id) REFERENCES dish (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_picture ADD CONSTRAINT FK_9E005237CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE menu_picture ADD CONSTRAINT FK_9E005237EE45BDBF FOREIGN KEY (picture_id) REFERENCES picture (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE order_menu ADD CONSTRAINT FK_30F40084A15A2E17 FOREIGN KEY (customer_order_id) REFERENCES `order` (id)');
        $this->addSql('ALTER TABLE order_menu ADD CONSTRAINT FK_30F40084CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id)');
        $this->addSql('ALTER TABLE `order` ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE `order` ADD CONSTRAINT FK_F5299398A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_F5299398A76ED395 ON `order` (user_id)');
        $this->addSql('ALTER TABLE review ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish_picture DROP FOREIGN KEY FK_866C551F148EB0CB');
        $this->addSql('ALTER TABLE dish_picture DROP FOREIGN KEY FK_866C551FEE45BDBF');
        $this->addSql('ALTER TABLE dish_allergen DROP FOREIGN KEY FK_3C4389A5148EB0CB');
        $this->addSql('ALTER TABLE dish_allergen DROP FOREIGN KEY FK_3C4389A56E775A4A');
        $this->addSql('ALTER TABLE menu_theme DROP FOREIGN KEY FK_6D9C46FCCD7E912');
        $this->addSql('ALTER TABLE menu_theme DROP FOREIGN KEY FK_6D9C46F59027487');
        $this->addSql('ALTER TABLE menu_diet DROP FOREIGN KEY FK_55AB956ECCD7E912');
        $this->addSql('ALTER TABLE menu_diet DROP FOREIGN KEY FK_55AB956EE1E13ACE');
        $this->addSql('ALTER TABLE menu_dish DROP FOREIGN KEY FK_5D327CF6CCD7E912');
        $this->addSql('ALTER TABLE menu_dish DROP FOREIGN KEY FK_5D327CF6148EB0CB');
        $this->addSql('ALTER TABLE menu_picture DROP FOREIGN KEY FK_9E005237CCD7E912');
        $this->addSql('ALTER TABLE menu_picture DROP FOREIGN KEY FK_9E005237EE45BDBF');
        $this->addSql('ALTER TABLE order_menu DROP FOREIGN KEY FK_30F40084A15A2E17');
        $this->addSql('ALTER TABLE order_menu DROP FOREIGN KEY FK_30F40084CCD7E912');
        $this->addSql('DROP TABLE dish_picture');
        $this->addSql('DROP TABLE dish_allergen');
        $this->addSql('DROP TABLE menu_theme');
        $this->addSql('DROP TABLE menu_diet');
        $this->addSql('DROP TABLE menu_dish');
        $this->addSql('DROP TABLE menu_picture');
        $this->addSql('DROP TABLE order_menu');
        $this->addSql('ALTER TABLE `order` DROP FOREIGN KEY FK_F5299398A76ED395');
        $this->addSql('DROP INDEX IDX_F5299398A76ED395 ON `order`');
        $this->addSql('ALTER TABLE `order` DROP user_id');
        $this->addSql('ALTER TABLE review DROP FOREIGN KEY FK_794381C6A76ED395');
        $this->addSql('DROP INDEX IDX_794381C6A76ED395 ON review');
        $this->addSql('ALTER TABLE review DROP user_id');
    }
}
