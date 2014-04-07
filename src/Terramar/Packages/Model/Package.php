<?php

namespace Terramar\Packages\Model;

class Package implements \Serializable
{
    private $name;
    
    private $description;

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
        
        $this->name = $data['name'];
        $this->description = $data['description'];
    }
}