# redis-client
```
a redis contains reconnect 
```

# usage

```
$this->client = new Client($this->host, $this->port);
$this->proxy = new Proxy($this->client);
$this->proxy->setLogger(new FakeLogger());

$this->proxy->set('hello', 'world');
$this->assertEquals($this->proxy->get('hello'), 'world');
$this->proxy->del('hello');
$this->assertEmpty($this->proxy->get('hello'));

$error = "";
$this->proxy->disconnect();
try {
    $this->proxy->set('hello', 'world');
} catch (\Exception $e) {
    $error = $e->getMessage();
}
$this->assertEmpty($error);

$this->client->set('hello', 'world');
$this->assertEquals($this->proxy->get('hello'), 'world');
```

