<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121005446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id)');
        $this->addSql('ALTER TABLE plan ADD description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3E899029B FOREIGN KEY (plan_id) REFERENCES plan (id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE users ADD reset_token VARCHAR(100) DEFAULT NULL, ADD report_attempts INT NOT NULL, CHANGE pseudo pseudo VARCHAR(125) DEFAULT NULL');
        $this->addSql('ALTER TABLE users ADD report_attempts INT DEFAULT 3 NOT NULL');
        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(200) DEFAULT NULL, email VARCHAR(100) DEFAULT NULL, subject VARCHAR(150) DEFAULT NULL, message LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3E899029B');
        $this->addSql('ALTER TABLE subscription DROP FOREIGN KEY FK_A3C664D3A76ED395');
        $this->addSql('ALTER TABLE invoice DROP FOREIGN KEY FK_906517449A1887DC');
        $this->addSql('ALTER TABLE plan DROP description');
        $this->addSql('ALTER TABLE users DROP reset_token, DROP report_attempts, CHANGE pseudo pseudo VARCHAR(150) DEFAULT NULL');
        $this->addSql('ALTER TABLE users DROP report_attempts');
        $this->addSql('DROP TABLE contact');
    }
}
