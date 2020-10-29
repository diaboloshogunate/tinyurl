<?php
namespace App\Service;

use Doctrine\Persistence\ManagerRegistry;
use InvalidArgumentException;

class TinyURL
{
    private $doctrine;
    private $chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
    private $charCount = 36;
    private $minLength = 5;
    private $maxLength = 9;
    private $offset;
    private $uniqueCount;
    private $isDirty = true;

    public function __construct(ManagerRegistry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * set the chars to use for strings
     * @param string $chars chars to use
     * @return $this
     */
    public function setChars(string $chars): TinyURL
    {
        $this->chars = $chars;
        $length = strlen($this->chars);
        if($length != $this->charCount) {
            $this->isDirty = true;
            $this->charCount = $length;
        }

        return $this;
    }

    /**
     * set the min/max length for string
     * @param int $min min length
     * @param int $max max length
     * @return $this
     */
    public function setLength(int $min, int $max): TinyURL
    {
        if($min > $max)
            throw new InvalidArgumentException("Min length must be smaller than max. Given min: $min, max: $max");
        
        $this->minLength = $min;
        $this->maxLength = $max;
        $this->isDirty = true;

        return $this;
    }

    /**
     * if dirty recalculate values needed for generating random string
     */
    private function preprocess(): void
    {
        if(!$this->isDirty) return;
        $this->offset = $this->getMinOffset($this->minLength);
        $this->uniqueCount = $this->countUniqueValues($this->maxLength);
        $this->isDirty = false;
    }

    /**
     * generate unique string based on class properties
     * @return string
     * @throws \Exception
     */
    public function genUniqueString(): string
    {
        $this->preprocess();
        $rand = random_int(0, $this->uniqueCount);
        $string = $this->intToString($this->offset + $rand);

        $tinyUrl = $this->doctrine
            ->getRepository(\App\Entity\TinyUrl::class)
            ->findOneBy(['short' => $string]);

        return $tinyUrl ? $this->genUniqueString() : $string;
    }

    /**
     * Get the number of possible unique values between two lengths with a given number of possible values per a position
     * position matters therefore az != za
     * @param int $length remaining length
     * @return int
     */
    private function countUniqueValues(int $length): int
    {
        if($length < $this->minLength) return -1;// -1 because 0 index
        return pow($this->charCount, $length) + $this->countUniqueValues($length-1);
    }

    /**
     * gets the offset needed so that 0 is a string of length $length
     * @param int $length minimum/remaining length
     * @return int
     */
    private function getMinOffset(int $length = 5): int
    {
        if($length == 0) return -1;// -1 for 0 index
        return pow($this->charCount, $length-1) + $this->getMinOffset($length-1);
    }

    /**
     * recursively convert int to string
     * @param int $i the integrer to convert to a string
     * @return string
     */
    private function intToString(int $i): string
    {
        return ($i >= $this->charCount ? $this->intToString((floor($i / $this->charCount) >> 0) - 1) : '')
            . $this->chars[$i % $this->charCount >> 0];
    }
}