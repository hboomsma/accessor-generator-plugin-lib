<?php
namespace Hostnet\Component\AccessorGenerator;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\ManyToMany;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;

/**
 * Process Column, ManyToMany, OneToOne, ManyToOne,
 * OneToMany and GeneratedValue Doctrine ORM annotations
 * and extract the type and relationship information.
 *
 * @author Hidde Boomsma <hboomsma@hostnet.nl>
 */
class DoctrineAnnotationProcessor implements AnnotationProcessorInterface
{
    /**
     * Process annotations of type:
     *  Column,
     *  GeneratedValue,
     *  ManyToMany,
     *  ManyToOne,
     *  OneToMany,
     *  OneToOne.
     *
     * @param  object              $annotation  object of a class annotated with @annotation
     * @param  PropertyInformation $information
     * @return void
     */
    public function processAnnotation($annotation, PropertyInformation $information)
    {
        switch (true) {
            case $annotation instanceof Column:
                // Process scalar value (db-wise) columns.
                $this->processColumn($annotation, $information);
                break;
            case $annotation instanceof GeneratedValue:
                // Generated value columns such as auto_increment
                // should not have a stetter function generated.
                // If the user insists on setting this collumn
                // a setter could be implemented by hand.
                $information->setGenerateSet(false);
                break;
            case $annotation instanceof ManyToMany:
            case $annotation instanceof ManyToOne:
                // We are one the owning side (db-wise) of a collection,
                // so we should generate add en remove methods.
                $information->setCollection(true);
                // Intentional fall-through
            case $annotation instanceof OneToMany:
            case $annotation instanceof OneToOne:
                // All relationships have a target type that can
                // be extracted and used as the column type.
                $information->setType($this->classType($annotation->targetEntity));
                break;
        }
    }

    /**
     * Process a Column Annotation, extraxt information
     * about scale and precision for decimal types, length
     * and size of string and integer types, if the column
     * may be null and if it should be a unique value.
     *
     * @param Column $column
     * @param PropertyInformationInterface $information
     */
    protected function processColumn(Column $column, PropertyInformation $information)
    {
        // Make sure not to override a previous set object type.
        if ($information->getType() && substr($information->getType(), 0, 1) !== '\\') {
            $information->setType($this->transformType($column->type));
        }
        $information->setFixedPointNumber(strtolower($column->type) === Type::DECIMAL);
        $information->setLength($column->length ?: 0);
        $information->setPrecision($column->precision);
        $information->setScale($column->scale);
        $information->setUnique($column->unique);
        $information->setNullable($column->nullable);
        $information->setIntegerSize($this->getIntegerSizeForType($column->type));
    }

    /**
     * Take the doctrine type and turn it into the corresponding PHP type.
     * Take notion that we differ from the default implementation for bigint
     * values. We treat them as integer (which if fine on a  64bit system) or
     * throw exceptions (in the set methods) if the value is too big for PHP to
     * handle.
     *
     * If no valid transformation is found, the type will not be changed and
     * returned.
     *
     * @see http://php.net/manual/en/language.types.php
     * @see http://php.net/manual/en/function.gettype.php (double vs float)
     * @see http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html
     * @param  string $type
     * @return string Valid PHP type
     */
    private function transformType($type)
    {
        if ($type == Type::BOOLEAN) {
            return 'boolean';
        } elseif ($type == Type::SMALLINT || $type == Type::BIGINT || $type == Type::INTEGER) {
            return 'integer';
        } elseif ($type == Type::DECIMAL || $type == Type::FLOAT) {
            return 'float';
        } elseif ($type == Type::TEXT || $type == Type::GUID || $type == Type::STRING) {
            return 'string';
        } elseif ($type == Type::BLOB /* binary will be added in doctrine 2.5 */) {
            return 'resource';
        } elseif ($type ==  Type::DATETIME || $type == Type::DATETIMETZ || $type == Type::DATE || $type == Type::TIME) {
            return '\\' . \DateTime::class;
        } elseif ($type == Type::SIMPLE_ARRAY || $type == Type::JSON_ARRAY || $type == Type::TARRAY) {
            return 'array';
        } elseif ($type == Type::OBJECT) {
            return 'object';
        } else {
            return $type;
        }
    }

    /**
     * Return the size of an integer type in bits.
     * This value can be used by the set methods to
     * validate that the value sent to the databse
     * will not be too big and chopped off.
     *
     * PHP does scale all int values automatically
     * up when they grow larger and eventually turn
     * them silently into a float.
     *
     * @see http://doctrine-dbal.readthedocs.org/en/latest/reference/types.html
     * @param  string $type
     * @return int
     */
    private function getIntegerSizeForType($type)
    {
        switch($type){
            case 'bool':
            case 'boolean':
                return 1;
            case 'smallint':
                return 16;
            case 'bigint':
                return 64;
            case 'int':
            case 'integer':
            default:
                return 32;
        }
    }

    /**
     * Add prefix slash to class type
     * if it was not there already and
     * return as new string.
     *
     * @param string $type
     * @return string
     */
    private function classType($type)
    {
        if (isset($type[0]) && $type[0] != '\\') {
            return "\\$type";
        } else {
            return $type;
        }
    }
}