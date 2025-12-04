<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251204184605 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Réordonne les colonnes de la table user pour suivre l’ordre métier demandé.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user`
            MODIFY `id` INT AUTO_INCREMENT NOT NULL FIRST,
            MODIFY `uuid` CHAR(36) NOT NULL AFTER `id`,
            MODIFY `first_name` VARCHAR(255) NOT NULL AFTER `uuid`,
            MODIFY `last_name` VARCHAR(255) NOT NULL AFTER `first_name`,
            MODIFY `email` VARCHAR(180) NOT NULL AFTER `last_name`,
            MODIFY `password` VARCHAR(255) NOT NULL AFTER `email`,
            MODIFY `roles` JSON NOT NULL AFTER `password`,
            MODIFY `guest_number` INT DEFAULT NULL AFTER `roles`,
            MODIFY `allergy` VARCHAR(255) DEFAULT NULL AFTER `guest_number`,
            MODIFY `api_token` VARCHAR(255) NOT NULL AFTER `allergy`,
            MODIFY `created_at` DATETIME NOT NULL AFTER `api_token`,
            MODIFY `updated_at` DATETIME DEFAULT NULL AFTER `created_at`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `user`
            MODIFY `id` INT AUTO_INCREMENT NOT NULL FIRST,
            MODIFY `email` VARCHAR(180) NOT NULL AFTER `id`,
            MODIFY `roles` JSON NOT NULL AFTER `email`,
            MODIFY `password` VARCHAR(255) NOT NULL AFTER `roles`,
            MODIFY `created_at` DATETIME NOT NULL AFTER `password`,
            MODIFY `updated_at` DATETIME DEFAULT NULL AFTER `created_at`,
            MODIFY `api_token` VARCHAR(255) NOT NULL AFTER `updated_at`,
            MODIFY `first_name` VARCHAR(255) NOT NULL AFTER `api_token`,
            MODIFY `last_name` VARCHAR(255) NOT NULL AFTER `first_name`,
            MODIFY `uuid` CHAR(36) NOT NULL AFTER `last_name`,
            MODIFY `guest_number` INT DEFAULT NULL AFTER `uuid`,
            MODIFY `allergy` VARCHAR(255) DEFAULT NULL AFTER `guest_number`');
    }
}