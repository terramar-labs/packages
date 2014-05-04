<?php

namespace Terramar\Packages\Model;

class Package implements \Serializable
{
    private $id;
    
    private $name;
    
    private $description;

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    
    /**
     * @return string
     */
    public function serialize()
    {
        return serialize(array(
                'id' => $this->id,
                'name' => $this->name,
                'description' => $this->description
            ));
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->description = $data['description'];
    }
}