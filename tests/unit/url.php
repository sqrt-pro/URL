<?php

require_once __DIR__ . '/../init.php';

use SQRT\URL;
use SQRT\URLImmutable;

class urlTest extends PHPUnit_Framework_TestCase
{
  function testArguments()
  {
    $u = new URL();

    $u->addArgument('world');
    $u->addArguments('of', 'nature');
    $u->addArgument('hello', true);

    $this->assertTrue($u->hasArgument(1), 'Проверка существующего аргумента');
    $this->assertCount(4, $u->getArguments(), 'Количество аргументов верное');
    $this->assertEquals('hello', $u->getArgument(1), 'Аргументы правильно сортированы');

    $this->assertFalse($u->hasArgument(5), 'Проверка несуществующего аргумента');
    $this->assertFalse($u->getArgument(5), 'Получение значения по-умолчанию');
    $this->assertEquals('wow', $u->getArgument(5, null, 'wow'), 'Получение заданного значения по-умолчанию');

    $v = $u->getArgument(1, 'is_numeric', 'wow');
    $this->assertEquals('wow', $v, 'Значение по-умолчанию для не прошедшего фильтр значения');

    $this->assertEquals('hello!!!', $u->getArgument(1, array($this, 'change')), 'Фильтр изменяющий значение');
    $this->assertEquals('hello', $u->getArgument(1), 'Значение не изменилось в объекте');

    $u->setArgument(2, 'from');
    $this->assertEquals('from', $u->getArgument(2), 'Установка значения аргумента');

    $u->setArgument(15, 'greedy', 'Номер аргумента превышает количество аргументов до него');
    $this->assertFalse($u->hasArgument(15), 'Аргумента №15 не добавлено');
    $this->assertEquals('greedy', $u->getArgument(5), 'Аргумент добавлен в конец');

    $u->removeArgument(2);

    $this->assertEquals('greedy', $u->getArgument(4), 'Аргументы сдвинулись после удаления');

    $u->removeArguments(array('one', 'two'));

    $this->assertFalse($u->hasArgument(3), 'Осталось только два аргумента');
    $this->assertEquals('one', $u->getArgument(1), 'Значение изменилось');
  }

  function testParameters()
  {
    $u = new URL('hello');

    $u->addParameter('one', 'two');
    $u->setParameter('one', 1);
    $u->addParameters(array('three' => 'four', 'hour' => 60));
    $u->addParameter('name', 'John');
    $u->addParameter('name', 'Doe');
    $u->addParameter('wow', 0);

    $exp = array(
      'hour'  => 60,
      'name'  => array('John', 'Doe'),
      'one'   => 1,
      'three' => 'four',
      'wow'   => 0
    );

    $this->assertEquals($exp, $u->getParameters(), 'Параметры заданы правильно и отсортированы по ключам');

    $this->assertEquals(1, $u->getParameter('one'), 'Значение параметра');
    $this->assertFalse($u->getParameter('one', '!^[a-z]+$!'), 'Значение не прошло валидацию');

    $this->assertTrue($u->hasParameter('wow'));
    $this->assertEquals(0, $u->getParameter('wow'), 'Нулевое значение корректно обрабатывается');

    $this->assertEquals('wow', $u->getParameter('unexist', null, 'wow'), 'Значение по-умолчанию для несуществующего параметра');

    $this->assertTrue($u->hasParameter('name'), 'Параметр name существует');
    $this->assertFalse($u->getParameter('name'), 'Параметр name является массивом, поэтому возвращает false на обычный геттер');
    $this->assertEquals(array('John', 'Doe'), $u->getParameterAsArray('name'), 'Параметр возвращается в виде массива');
    $this->assertEquals(array(1), $u->getParameterAsArray('one'), 'Можно получить массив из одного элемента');

    $this->assertEquals('four!!!', $u->getParameter('three', array($this, 'change')), 'Фильтр изменяющий значение');
    $this->assertEquals('four', $u->getParameter('three'), 'Значение не изменилось в объекте');

    $u->removeParameter('one');
    $this->assertFalse($u->hasParameter('one'), 'Удаление параметра');
    $this->assertCount(4, $u->getParameters(), 'Количество уменьшилось');

    $this->assertFalse($u->getId(), 'Параметр ID не задан');
    $this->assertEquals(1, $u->getPage(), 'Параметр Page значение по-умолчанию');

    $u->removeParameters(array('id' => 12, 'page' => 3));
    $this->assertFalse($u->hasParameter('name'), 'Старые параметры удалены');
    $this->assertEquals(12, $u->getId(), 'Новые параметры подставлены');
    $this->assertEquals(3, $u->getPage(), 'Параметр Page задан');
  }

  function testFileName()
  {
    $u = new URL('hello', 'world');

    $this->assertFalse($u->hasFileName(), 'Имя файла явно не задано');
    $this->assertEquals('world', $u->getFileName(), 'Если имя файла не задано, берется последний аргумент');
    $this->assertFalse($u->getFileExtension(), 'Расширение файла не известно');
    $this->assertEquals('txt', $u->getFileExtension('txt'), 'Расширение файла по-умолчанию');

    $u->setFileExtension('txt');
    $this->assertTrue($u->hasFileName(), 'Если задано расширение без имени файла - последний аргумент будет превращен в имя файла');
    $this->assertEquals('world.txt', $u->getFileName(true), 'Последний аргумент + расширение');
    $this->assertCount(1, $u->getArguments(), 'Количество аргументов уменьшилось');

    $u->setFileName('promo.days.html');

    $this->assertEquals('promo.days.html', $u->getFileName(), 'Имя файла задано');
    $this->assertEquals('html', $u->getFileExtension(), 'Расширение определяется корректно');
  }

  function testHost()
  {
    $u = new URL();

    $this->assertEquals('localhost', $u->getHost(), 'Имя хоста по-умолчанию');
    $this->assertEquals('http', $u->getScheme(), 'Схема HTTP по-умолчанию');
    $this->assertFalse($u->hasSubDomain(2), 'Нет поддоменов 2го уровня');
    $this->assertEquals('ru', $u->getSubDomain(2, null, 'ru'), 'Поддомен по-умолчанию, когда значения нет');

    $u->setHost('hello.world.ru');
    $this->assertEquals('hello.world.ru', $u->getHost(), 'Имя хоста');
    $this->assertEquals('http', $u->getScheme(), 'Схема не изменилась');
    $this->assertTrue($u->hasSubDomain(3), 'Есть поддомен 3го уровня');
    $this->assertEquals('hello', $u->getSubDomain(3), 'Поддомен 3го уровня');

    $u->setHost('ftp://ololo.ru/');
    $this->assertEquals('ololo.ru', $u->getHost(), 'Получение хоста без схемы');
    $this->assertEquals('ftp', $u->getScheme(), 'Схема изменилась');

    $u->addSubDomain('welcome');
    $u->setSubDomain(1, 'com');
    $this->assertEquals('welcome.ololo.com', $u->getHost(), 'Хост изменился');
  }

  function testConstruct()
  {
    $u = new URL();
    $u->parse('/hello/world/id:123/');
    $this->assertCount(2, $u->getArguments(), '2 Аргумента парсинг через метод parse');
    $this->assertEquals(123, $u->getId(), 'ID задан через метод parse');

    $u = new URL('/hello/world/id:123/');
    $this->assertCount(2, $u->getArguments(), '2 Аргумента парсинг через конструктор из строки');
    $this->assertEquals(123, $u->getId(), 'ID задан через конструктор из строки');

    $u = new URL('hello', array('id' => 123), 'world');
    $this->assertCount(2, $u->getArguments(), '2 Аргумента парсинг через конструктор из аргументов');
    $this->assertEquals(123, $u->getId(), 'ID задан через конструктор из аргументов');
  }

  function testBuildString()
  {
    $u = new URL();
    $this->assertEquals('/', $u->asString(), 'Пустой относительный путь');
    $this->assertEquals('http://localhost/', $u->asString(true), 'Пустой абсолютный путь');

    $u = new URL('hello', 'world');
    $this->assertEquals('/hello/world/', $u->asString(), 'Относительный путь');
    $this->assertEquals('http://localhost/hello/world/', $u->asString(true), 'Абсолютный путь');

    $u = new URL('hello', 'world', array('id' => 12, 'name' => 'john'), 'bird.html');
    $this->assertEquals('/hello/world/id:12/name:john/bird.html', $u->asString(), 'Параметры и файл');
  }

  function testParseFileName()
  {
    $u = new URL('/hello/world/file.html');

    $this->assertCount(2, $u->getArguments(), '2 аргумента');
    $this->assertEquals('file.html', $u->getFileName(true), 'Имя файла');
    $this->assertEquals('html', $u->getFileExtension(), 'Расширение файла');
  }

  function testParseAbsolutePath()
  {
    $u = new URL('ftp://hello.ololo.ru/some/path/with:params/');

    $this->assertEquals('ftp', $u->getScheme(), 'Схема');
    $this->assertEquals('hello', $u->getSubDomain(3), 'Поддомен 3 уровня');
    $this->assertEquals('path', $u->getArgument(2), 'Аргумент');
    $this->assertEquals('params', $u->getParameter('with'), 'Параметр');
  }

  function testBuildTag()
  {
    $u = new URL('hello', 'world');
    $this->assertInstanceOf('SQRT\\Tag\\A', $u->asTag(), 'Объект Tag::A');

    $exp = '<a href="/hello/world/">Hello</a>';
    $this->assertEquals($exp, (string)$u->asTag('Hello'), 'Относительная ссылка подставлена');

    $exp = '<a class="my" href="http://localhost/hello/world/" target="_blank">Hello</a>';
    $this->assertEquals($exp, (string)$u->asTag('Hello', 'my', '_blank', true), 'Абсолютная ссылка подставлена');
  }

  function testNonAscii()
  {
    $u = new URL('http://ололо.рф/привет/мир/один:два/фаел.хтмл');

    $this->assertEquals('ололо.рф', $u->getHost(), 'Хост');
    $this->assertEquals('привет', $u->getArgument(1), 'Аргументы');
    $this->assertEquals('два', $u->getParameter('один'), 'Параметры');
    $this->assertEquals('фаел.хтмл', $u->getFileName(), 'Файл');

    $this->assertEquals(
      'http://xn--k1aahbb.xn--p1ai/%D0%BF%D1%80%D0%B8%D0%B2%D0%B5%D1%82/%D0%BC%D0%B8%D1%80/%D0%BE%D0%B4%D0%B8%D0%BD:%D0%B4%D0%B2%D0%B0/%D1%84%D0%B0%D0%B5%D0%BB.%D1%85%D1%82%D0%BC%D0%BB',
      $u->asString(true),
      'Все части приведены в Punycode и Urlencode'
    );

    $u = new URL('http://xn--k1aahbb.xn--p1ai/%D0%BF%D1%80%D0%B8%D0%B2%D0%B5%D1%82/%D0%BC%D0%B8%D1%80/%D0%BE%D0%B4%D0%B8%D0%BD:%D0%B4%D0%B2%D0%B0/%D1%84%D0%B0%D0%B5%D0%BB.%D1%85%D1%82%D0%BC%D0%BB');
    $this->assertEquals('ололо.рф', $u->getHost(), 'Хост преобразован');
    $this->assertEquals('привет', $u->getArgument(1), 'Аргументы преобразованы');
    $this->assertEquals('два', $u->getParameter('один'), 'Параметры преобразованы');
    $this->assertEquals('фаел.хтмл', $u->getFileName(true), 'Название файла');
    $this->assertEquals('хтмл', $u->getFileExtension(), 'Разрешение');
  }

  function testImmutable()
  {
    $u1 = new URLImmutable('hello', 'world');

    $this->assertEquals('/hello/world/', $u1->asString(), 'Начальное состояние');

    $u2 = $u1->removeArguments()
             ->removeParameters()
             ->addParameter('id', 12)
             ->addArgument('ololo')
             ->addSubDomain('www')
             ->setScheme('ftp')
             ->addParameters(array('name' => 'john'))
             ->addArguments('one', 'two');

    $this->assertEquals('/hello/world/', $u1->asString(), 'Изначальный объект не изменился');
    $this->assertEquals('ftp://www.localhost/ololo/one/two/id:12/name:john/', $u2->asString(true), 'Изменения в новом объекте');
  }

  function change(&$var)
  {
    $var .= '!!!';

    return true;
  }
}