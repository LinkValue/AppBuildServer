<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170915074948 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE appbuild_user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(500) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', enabled TINYINT(1) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appbuild_application (id INT AUTO_INCREMENT NOT NULL, `label` VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, support VARCHAR(255) NOT NULL, package_name VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX label_support_idx (`label`, support), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appbuild_application_user (application_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_85DB94FA3E030ACD (application_id), INDEX IDX_85DB94FAA76ED395 (user_id), PRIMARY KEY(application_id, user_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appbuild_build (id INT AUTO_INCREMENT NOT NULL, application_id INT DEFAULT NULL, version VARCHAR(255) NOT NULL, comment LONGTEXT DEFAULT NULL, file_path VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_C828DED43E030ACD (application_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE appbuild_build_token (id INT AUTO_INCREMENT NOT NULL, build_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, expired_at DATETIME NOT NULL, INDEX IDX_905F928F17C13F8B (build_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE appbuild_application_user ADD CONSTRAINT FK_85DB94FA3E030ACD FOREIGN KEY (application_id) REFERENCES appbuild_application (id)');
        $this->addSql('ALTER TABLE appbuild_application_user ADD CONSTRAINT FK_85DB94FAA76ED395 FOREIGN KEY (user_id) REFERENCES appbuild_user (id)');
        $this->addSql('ALTER TABLE appbuild_build ADD CONSTRAINT FK_C828DED43E030ACD FOREIGN KEY (application_id) REFERENCES appbuild_application (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE appbuild_build_token ADD CONSTRAINT FK_905F928F17C13F8B FOREIGN KEY (build_id) REFERENCES appbuild_build (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE appbuild_application_user DROP FOREIGN KEY FK_85DB94FAA76ED395');
        $this->addSql('ALTER TABLE appbuild_application_user DROP FOREIGN KEY FK_85DB94FA3E030ACD');
        $this->addSql('ALTER TABLE appbuild_build DROP FOREIGN KEY FK_C828DED43E030ACD');
        $this->addSql('ALTER TABLE appbuild_build_token DROP FOREIGN KEY FK_905F928F17C13F8B');
        $this->addSql('DROP TABLE appbuild_user');
        $this->addSql('DROP TABLE appbuild_application');
        $this->addSql('DROP TABLE appbuild_application_user');
        $this->addSql('DROP TABLE appbuild_build');
        $this->addSql('DROP TABLE appbuild_build_token');
    }
}
