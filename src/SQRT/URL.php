<?php

namespace SQRT;

use SQRT\Tag\A;
use True\Punycode;

class URL
{
  protected static $default_domain = 'localhost';

  protected $host;
  protected $scheme;
  protected $arguments  = array();
  protected $parameters = array();
  protected $subdomains = array();
  protected $file_name;
  protected $file_extension;
  protected $locked;

  function __construct($url = null, $_ = null)
  {
    $this->setHost('localhost');

    $arr = func_get_args();
    foreach ($arr as $v) {
      if (!is_null($v)) {
        $this->parse($v, false);
      }
    }

    $this->locked = true;
  }

  function __toString()
  {
    return $this->asString();
  }

  /**
   * Разбор адреса. Если передан относительный адрес, хост и схема по-умолчанию будут http://localhost
   * $cleanup - нужно ли очищать предыдущие данные
   */
  public function parse($url, $cleanup = true)
  {
    if ($cleanup) {
      $this->removeArguments();
      $this->removeParameters();
      $this->setFileName(false);
      $this->setHost('localhost');
    }

    if (is_array($url)) {
      $this->addParameters($url);
    } else {

      if ($this->checkSchemeExists($url)) {
        $a = parse_url($url);
        if (!empty($a['host'])) {
          $this->setHost($a['host']);
        }
        if (!empty($a['scheme'])) {
          $this->setScheme($a['scheme']);
        }

        $url = $a['path'];
      }
      $url = trim($url, '/');
      $arr = array_filter(explode('/', urldecode($url)));

      if (empty($arr)) {
        return false;
      }

      $total = count($arr);
      foreach ($arr as $i => $str) {
        if (strpos($str, ':')) {
          $a = explode(':', $str, 2);
          if (count($a) == 2) {
            $this->addParameter($a[0], $a[1]);
          }
        } else {
          if ($total == $i + 1 && strpos($str, '.')) {
            $this->setFileName($str);
          } else {
            $this->addArgument($str);
          }
        }
      }
    }

    return $this;
  }

  /** Полный домен */
  public function setHost($host)
  {
    if (!$this->checkSchemeExists($host)) {
      $host = 'http://' . $host;
    }

    $a = parse_url($host);
    $this->setScheme($a['scheme']);

    $puny = new Punycode();
    $this->host = $puny->decode($a['host']);

    $a = explode('.', $this->host);
    krsort($a);
    $this->subdomains = $a;

    return $this->sortSubdomains();
  }

  /** Полный домен */
  public function getHost()
  {
    return $this->host;
  }

  /** Схема */
  public function getScheme()
  {
    return !empty($this->scheme) ? $this->scheme : 'http';
  }

  /** Схема */
  public function setScheme($scheme)
  {
    $this->scheme = $scheme;

    return $this;
  }

  /** Проверка есть ли поддомен. Нумерация с конца, с "1" */
  public function hasSubDomain($level)
  {
    return isset($this->subdomains[$level]);
  }

  /**
   * Получить значение поддомена. Нумерация с конца, с "1"
   * $filter - callable или regexp
   * $default - значение по-умолчанию, если значение не задано или не проходит фильтр
   */
  public function getSubDomain($level, $filter = null, $default = false)
  {
    if (!$this->hasSubDomain($level)) {
      return $default;
    }

    return static::FilterValue($this->subdomains[$level], $filter, $default);
  }

  /** Установить поддомен. Нумерация с конца, с "1" */
  public function setSubDomain($level, $subdomain)
  {
    $this->subdomains[$level] = $subdomain;

    return $this->sortSubdomains();
  }

  /** Добавить поддомен */
  public function addSubDomain($subdomain)
  {
    $this->subdomains[] = $subdomain;

    return $this->sortSubdomains();
  }

  /** Проверка, есть ли аргумент. Нумерация с "1" */
  public function hasArgument($num)
  {
    return isset($this->arguments[$num]);
  }

  /**
   * Получить значение аргумента. Нумерация с "1"
   * $filter - callable или regexp
   * $default - значение по-умолчанию, если значение не задано или не проходит фильтр
   */
  public function getArgument($num, $filter = null, $default = false)
  {
    if (!$this->hasArgument($num)) {
      return $default;
    }

    $v = $this->arguments[$num];

    return static::FilterValue($v, $filter, $default);
  }

  /** Список всех аргументов. Нумерация с "1" */
  public function getArguments()
  {
    return $this->arguments;
  }

  /** Установить значение аргумента. Нумерация с "1" */
  public function setArgument($num, $argument)
  {
    $this->arguments[$num] = $argument;

    $this->sortArguments();

    return $this;
  }

  /**
   * Установить значения аргументов. Значения могут быть указаны как массив или как список аргументов функции.
   * Предыдущие значения затираются в соответствии по ключам
   */
  public function addArguments($mixed, $_ = null)
  {
    if (!is_array($mixed)) {
      $mixed = func_get_args();
    }

    foreach ($mixed as $arg) {
      $this->addArgument($arg);
    }

    return $this;
  }

  /** Убрать значение аргумента. Остальные значения сдвигаются! */
  public function removeArgument($num)
  {
    if ($this->hasArgument($num)) {
      unset($this->arguments[$num]);
    }

    return $this->sortArguments();
  }

  /** Убрать все аргументы. Можно сразу задать массив новых аргументов */
  public function removeArguments(array $arguments = null)
  {
    $this->arguments = array();

    if ($arguments) {
      $this->addArguments($arguments);
    }

    return $this;
  }

  /**
   * Добавить аргумент в конец строки.
   * Параметр $prepend позволяет вставить элемент в начало строки. Остальные значения сдвигаются!
   */
  public function addArgument($argument, $prepend = false)
  {
    if ($prepend) {
      array_unshift($this->arguments, $argument);
    } else {
      array_push($this->arguments, $argument);
    }

    return $this->sortArguments();
  }

  /** Проверка, есть ли параметр */
  public function hasParameter($name)
  {
    return isset($this->parameters[$name]);
  }

  /**
   * Получить значение параметра
   * $filter - callable или regexp
   * $default - значение по-умолчанию, если значение не задано или не проходит фильтр
   */
  public function getParameter($name, $filter = null, $default = false)
  {
    if (!$this->hasParameter($name) || is_array($this->parameters[$name])) {
      return $default;
    }

    $v = $this->parameters[$name];

    return static::FilterValue($v, $filter, $default);
  }

  /**
   * Получить значение параметра в виде массива.
   * Если передан один параметр - он будет в массиве
   * $filter - callable или regexp, в результате будет оставлены только значения проходящие фильтр
   * $default - значение по-умолчанию, если значение не задано или не проходит фильтр
   */
  public function getParameterAsArray($name, $filter = null, $default = array())
  {
    if (!$this->hasParameter($name)) {
      return $default;
    }

    return static::FilterArray((array)$this->parameters[$name], $filter, $default);
  }

  /** Список всех параметров */
  public function getParameters()
  {
    return $this->parameters;
  }

  /** Установка значения параметра. Если параметр уже задан - он будет перезаписан */
  public function setParameter($name, $parameter)
  {
    $this->parameters[$name] = $parameter;

    return $this->sortParameters();
  }

  /** Добавление параметра. Если такой параметр уже задан, значение будет добавлено в массив */
  public function addParameter($name, $parameter)
  {
    if ($this->hasParameter($name)) {
      $this->parameters[$name]   = (array)$this->parameters[$name];
      $this->parameters[$name][] = $parameter;
    } else {
      $this->parameters[$name] = $parameter;
    }

    return $this->sortParameters();
  }

  /** Установка группы параметров */
  public function addParameters(array $array)
  {
    foreach ($array as $key => $val) {
      $this->addParameter($key, $val);
    }

    return $this->sortParameters();
  }

  /** Удаление заданного параметра */
  public function removeParameter($name)
  {
    if ($this->hasParameter($name)) {
      unset($this->parameters[$name]);
    }

    return $this;
  }

  /** Удаление всех параметров. Можно сразу задать массив новых параметров */
  public function removeParameters(array $parameters = null)
  {
    $this->parameters = array();

    if ($parameters) {
      $this->addParameters($parameters);
    }

    return $this;
  }

  /** Задано ли имя файла, как один из аргументов */
  public function hasFileName()
  {
    return !empty($this->file_name);
  }

  /**
   * Получить имя файла. Если оно не задано, будет возвращено значение последнего аргумента
   * $strict - жесткая проверка, если файл не задан - вернется false
   */
  public function getFileName($strict = false)
  {
    if (!$this->hasFileName()) {
      if ($strict) {
        return false;
      }

      $arr = $this->getArguments();

      return current(array_slice($arr, -1));
    }

    return $this->file_name;
  }

  /** Задать имя файла */
  public function setFileName($filename)
  {
    $a = explode('.', $filename);

    $this->file_name      = $filename;
    $this->file_extension = count($a) >= 2 ? array_pop($a) : false;

    return $this;
  }

  /** Получить расширение файла из запроса */
  public function getFileExtension($default = false)
  {
    return !empty($this->file_extension) ? $this->file_extension : $default;
  }

  /** Установить расширение файла. Если имя файла не задано, последний аргумент будет конвертирован в имя файла */
  public function setFileExtension($extension)
  {
    if ($this->hasFileName()) {
      $a = explode('.', $this->getFileName(true));
      if (count($a) >= 2) {
        array_pop($a);
      }
      $a[]             = $extension;
      $this->file_name = join('.', $a);
    } else {
      $this->file_name = array_pop($this->arguments) . '.' . $extension;
    }

    return $this;
  }

  /** Получение ID из параметра */
  public function getId($as_array = false, $default = false)
  {
    $f = function ($var) {
      return is_numeric($var) && $var > 0;
    };

    return $as_array ? $this->getParameterAsArray('id', $f, $default) : $this->getParameter('id', $f, $default);
  }

  /** Установка ID в параметрах */
  public function setId($id)
  {
    return $this->setParameter('id', $id);
  }

  /** Получение номера страницы. По-умолчанию значение 1 */
  public function getPage()
  {
    $f = function ($var) {
      return is_numeric($var) && $var > 0;
    };

    return $this->getParameter('page', $f, 1);
  }

  /** Установка номера страницы */
  public function setPage($page)
  {
    $this->setParameter('page', $page);
  }

  /** Возвратить адрес как строку */
  public function asString($absolute = false)
  {
    $url = '/';
    if ($absolute) {
      $puny = new Punycode();
      $url = $this->getScheme() . '://' . $puny->encode($this->getHost()) . '/';
    }

    if ($a = $this->getArguments()) {
      foreach ($this->getArguments() as $str) {
        $url .= urlencode($str) . '/';
      }
    }

    $url .= $this->buildParams();

    if ($fn = $this->getFileName(true)) {
      $url .= urlencode($fn);
    }

    return $url;
  }

  /** @return \SQRT\Tag\A */
  public function asTag($value = null, $attr = null, $target = null, $absolute = false)
  {
    return new A($this->asString($absolute), $value, $attr, $target);
  }

  /** Проверка указана ли схема в адресе */
  protected function checkSchemeExists($url)
  {
    return preg_match('!^([a-z]+):\/\/!', $url);
  }

  /** Построение строки параметров */
  protected function buildParams($arr = null, $force_key = false)
  {
    if (is_null($arr)) {
      $arr = $this->getParameters();
    }

    if (empty($arr)) {
      return false;
    }

    $out = '';
    foreach ($arr as $key => $val) {
      if (is_array($val)) {
        $out .= $this->buildParams($val, $key);
      } else {
        $out .= urlencode($force_key ?: $key) . ':' . urlencode($val) . '/';
      }
    }

    return $out;
  }

  /** Сортировка аргументов */
  protected function sortArguments()
  {
    $i   = 0;
    $res = array();

    foreach ($this->arguments as $val) {
      $res[++$i] = $val;
    }

    $this->arguments = $res;

    return $this;
  }

  /** Сортировка параметров */
  protected function sortParameters()
  {
    ksort($this->parameters);

    return $this;
  }

  /** Сортировка субдоменов */
  protected function sortSubdomains()
  {
    $i   = 0;
    $res = array();

    foreach ($this->subdomains as $val) {
      $res[++$i] = $val;
    }

    $this->subdomains = $res;
    krsort($res);
    $this->host = join('.', $res);

    return $this;
  }

  /**
   * Фильтрация значений.
   * Возвращает значение, если оно проходит фильтр или $default
   * $filter - callable, regexp или список допустимых значений
   * Значение в фильтр передается по ссылке!
   */
  public static function FilterValue(&$val, $filter = null, $default = false)
  {
    $ok = true;
    if (is_callable($filter)) {
      $ok = call_user_func_array($filter, array(&$val));
    } elseif (is_array($filter)) {
      $ok = in_array($val, $filter);
    } elseif (!empty($filter)) {
      $ok = preg_match($filter, $val);
    }

    return $ok ? $val : $default;
  }

  /**
   * Фильтрация значений массива. Возвращает результирующий массив.
   * Оставляет в результирующем массиве только допустимые значения, или $default, если допустимых значений нет.
   * Callable может принимать значение по ссылке и изменять его для выводного массива
   */
  public static function FilterArray($array, $filter = null, $default = array())
  {
    if (empty($array) || !is_array($array)) {
      return $default;
    }

    $out = false;
    foreach ($array as $key => $val) {
      if (!is_null(static::FilterValue($val, $filter, null))) {
        $out[$key] = $val;
      }
    }

    return $out ?: $default;
  }

  /** Установка домена по-умолчанию, если он не указан явно */
  public static function SetDomain($domain)
  {
    static::$default_domain = $domain;
  }

  /** Домен по-умолчанию, если он не указан явно */
  public static function GetDomain()
  {
    return static::$default_domain;
  }
}