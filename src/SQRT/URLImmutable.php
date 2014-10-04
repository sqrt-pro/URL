<?php

namespace SQRT;

class URLImmutable extends URL
{
  protected function getObj()
  {
    $u = new URLImmutable($this->asString(true));
    $u->setLocked(false);

    return $u;
  }

  /** Блокировка изменений */
  public function isLocked()
  {
    return (bool)$this->locked;
  }

  /** Блокировка изменений */
  public function setLocked($locked = true)
  {
    $this->locked = $locked;

    return $this;
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function addSubDomain($subdomain)
  {
    if ($this->isLocked()) {
      return $this->getObj()->addSubDomain($subdomain)->setLocked();
    } else {
      return parent::addSubDomain($subdomain);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function addArguments($mixed, $_ = null)
  {
    if (!is_array($mixed)) {
      $mixed = func_get_args();
    }

    if ($this->isLocked()) {
      return $this->getObj()->addArguments($mixed)->setLocked();
    } else {
      return parent::addArguments($mixed);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function addArgument($argument, $prepend = false)
  {
    if ($this->isLocked()) {
      return $this->getObj()->addArgument($argument, $prepend)->setLocked();
    } else {
      return parent::addArgument($argument, $prepend);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function addParameter($name, $parameter)
  {
    if ($this->isLocked()) {
      return $this->getObj()->addParameter($name, $parameter)->setLocked();
    } else {
      return parent::addParameter($name, $parameter);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function addParameters(array $array)
  {
    if ($this->isLocked()) {
      return $this->getObj()->addParameters($array)->setLocked();
    } else {
      return parent::addParameters($array);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setHost($host)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setHost($host)->setLocked();
    } else {
      return parent::setHost($host);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setScheme($scheme)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setScheme($scheme)->setLocked();
    } else {
      return parent::setScheme($scheme);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setSubDomain($level, $subdomain)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setSubDomain($level, $subdomain)->setLocked();
    } else {
      return parent::setSubDomain($level, $subdomain);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setArgument($num, $argument)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setArgument($num, $argument)->setLocked();
    } else {
      return parent::setArgument($num, $argument);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setParameter($name, $parameter)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setParameter($name, $parameter)->setLocked();
    } else {
      return parent::setParameter($name, $parameter);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setFileName($filename)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setFileName($filename)->setLocked();
    } else {
      return parent::setFileName($filename);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setFileExtension($extension)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setFileExtension($extension)->setLocked();
    } else {
      return parent::setFileExtension($extension);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setId($id)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setId($id)->setLocked();
    } else {
      return parent::setId($id);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function setPage($page)
  {
    if ($this->isLocked()) {
      return $this->getObj()->setPage($page)->setLocked();
    } else {
      return parent::setPage($page);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function parse($url, $cleanup = true)
  {
    if ($this->isLocked()) {
      return $this->getObj()->parse($url, $cleanup)->setLocked();
    } else {
      return parent::parse($url, $cleanup);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function removeArgument($num)
  {
    if ($this->isLocked()) {
      return $this->getObj()->removeArgument($num)->setLocked();
    } else {
      return parent::removeArgument($num);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function removeArguments(array $arguments = null)
  {
    if ($this->isLocked()) {
      return $this->getObj()->removeArguments($arguments)->setLocked();
    } else {
      return parent::removeArguments($arguments);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function removeParameter($name)
  {
    if ($this->isLocked()) {
      return $this->getObj()->removeParameter($name)->setLocked();
    } else {
      return parent::removeParameter($name);
    }
  }

  /**
   * {@inheritdoc}
   * @return URLImmutable
   */
  public function removeParameters(array $parameters = null)
  {
    if ($this->isLocked()) {
      return $this->getObj()->removeParameters($parameters)->setLocked();
    } else {
      return parent::removeParameters($parameters);
    }
  }
}