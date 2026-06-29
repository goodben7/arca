<?php
namespace App\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AbstractIdGenerator;

/**
 * Custom ID generator for entities
 * 
 * Generates IDs in format: PREFIX + RANDOM_LETTERS + DATE_TIME
 * Each entity using this generator must define an ID_PREFIX constant
 */
class IdGenerator extends AbstractIdGenerator 
{
    /**
     * Characters used for generating random letters
     */
    private const string CHARACTERS = 'abcdefghijklmnopqrstuvwxyz';
    
    /**
     * Default length for random letters part
     */
    private const int DEFAULT_RANDOM_LENGTH = 4;
    
    /**
     * DateTime format used in ID generation
     */
    private const string DATETIME_FORMAT = 'mdHis';
    
    private const int ID_MAX_LENGTH = 16;

    /**
     * Generates a unique ID for an entity
     *
     * @param EntityManagerInterface $em The entity manager
     * @param object|null $entity The entity for which to generate an ID
     * @return mixed The generated ID
     * @throws \LogicException If entity doesn't have ID_PREFIX constant
     */
    public function generateId(EntityManagerInterface $em, object|null $entity): mixed
    {
        if (!defined(get_class($entity) . '::ID_PREFIX')) {
            throw new \LogicException(sprintf('Entity %s must define an ID_PREFIX constant', get_class($entity)));
        }

        $prefix = $entity::ID_PREFIX;
        $currentDateTime = new \DateTime();
        $dateTimeString = $currentDateTime->format(self::DATETIME_FORMAT);
        $randomLength = self::ID_MAX_LENGTH - strlen($prefix) - strlen($dateTimeString);

        if ($randomLength < 1) {
            throw new \LogicException(sprintf('ID_PREFIX %s is too long for ID generation', $prefix));
        }

        $randomLetters = $this->generateRandomLetters($randomLength);

        return $prefix.strtoupper($randomLetters.$dateTimeString);
    }

    /**
     * Generates a string of random letters
     * 
     * @param int $length The length of the random string
     * @return string The generated random string
     */
    private function generateRandomLetters(int $length): string
    {
        $randomLetters = '';
        $charactersLength = strlen(self::CHARACTERS) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $randomLetters .= self::CHARACTERS[random_int(0, $charactersLength)];
        }
        
        return $randomLetters;
    }
}