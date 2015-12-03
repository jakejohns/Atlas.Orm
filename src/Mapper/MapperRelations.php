<?php
namespace Atlas\Orm\Mapper;

use Atlas\Orm\Exception;
use Atlas\Orm\Relation\ManyToOne;
use Atlas\Orm\Relation\OneToMany;
use Atlas\Orm\Relation\ManyToMany;
use Atlas\Orm\Relation\OneToOne;

class MapperRelations
{
    protected $relations = [];

    protected $mapperLocator;

    protected $nativeMapperClass;

    protected $fields = [];

    public function __construct(MapperLocator $mapperLocator)
    {
        $this->mapperLocator = $mapperLocator;
        $this->nativeMapperClass = substr(get_class($this), 0, -9) . 'Mapper';
        $this->setRelations();
    }

    protected function setRelations()
    {
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function set($name, $relationClass, $foreignMapperClass, $throughName = null)
    {
        if (! class_exists($foreignMapperClass)) {
            throw Exception::classDoesNotExist($foreignMapperClass);
        }

        if ($throughName && ! isset($this->relations[$throughName])) {
            throw Exception::relationDoesNotExist($throughName);
        }

        $relation = $this->newRelation($name, $relationClass, $foreignMapperClass, $throughName);
        $this->fields[$name] = null;
        $this->relations[$name] = $relation;
        return $relation;
    }

    protected function newRelation($name, $relationClass, $foreignMapperClass, $throughName = null)
    {
        return new $relationClass(
            $this->mapperLocator,
            $this->nativeMapperClass,
            $name,
            $foreignMapperClass,
            $throughName
        );
    }

    public function stitchIntoRecord(Record $record, array $with = [])
    {
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->stitchIntoRecord(
                $record,
                $custom
            );
        }
    }

    public function stitchIntoRecordSet(RecordSet $recordSet, array $with = [])
    {
        foreach ($this->fixWith($with) as $name => $custom) {
            $this->relations[$name]->stitchIntoRecordSet(
                $recordSet,
                $custom
            );
        }
    }

    protected function fixWith($spec)
    {
        $with = [];
        foreach ($spec as $key => $val) {
            if (is_int($key)) {
                $with[$val] = null;
            } else {
                $with[$key] = $val;
            }
        }
        return $with;
    }

    protected function oneToOne($name, $foreignMapperClass)
    {
        return $this->set(
            $name,
            OneToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function oneToMany($name, $foreignMapperClass)
    {
        return $this->set(
            $name,
            OneToMany::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToOne($name, $foreignMapperClass)
    {
        $this->set(
            $name,
            ManyToOne::CLASS,
            $foreignMapperClass
        );
    }

    protected function manyToMany($name, $foreignMapperClass, $throughName)
    {
        return $this->set(
            $name,
            ManyToMany::CLASS,
            $foreignMapperClass,
            $throughName
        );
    }
}