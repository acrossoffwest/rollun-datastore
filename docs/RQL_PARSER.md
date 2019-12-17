# DataStore Rql

##rollun/datastore/Rql/RqlQuery

RqlQuery наследник [`Xiag\Rql\Parser\Query`](https://github.com/xiag-ag/rql-parser).  

Данный обьект расширяет оригинал добавля ноду групировки, 
а так же позволяет инициализировать обьект спомощью rql выражения.

Для того что бы инициализировать обьект, достаточно в конструкторе передать rql выражение.
Пример:  

```php
    $query = new RqlQuery('eq(a,1)&select(a,b)');
```

Так же доступна нода `GroupBy`

## rollun\datastore\Rql\Node\Groupby
Нода позволяет делать групировки в запросе.

## rollun\datastore\Rql\Node\AggregateSelectNode
Нода которая перекрывает `SelectNode` добавляя возможность использовать ноду `AggregateFunctionNode`

## rollun\datastore\Rql\Node\AggregateFunctionNode
Нода которая дает возмождность делать агрегатные запросы.
#### Поддерживаемы агрегатные функции
* min - находит минимальный элемент выбраной группы.
* max - находит максимальный элемент выбраной группы.
* count - считает количество элементов выбраной группы.
* avg - вычесляет среднее значение элементов выбраной группы.
* sum - вычесляет сумму значений элементов выбраной группы.

## rollun\datastore\Rql\Node\LikeGlobNode
Like нода которая проверяет прищедшее ей значение и если оно не Glob, она заварачивает его в Glob.

## rollun\datastore\Rql\Node\ContainsNode
Нода которя позволяет исползовать 
Contains который выберет все обьекты которые включают всебя переданое значение в указаном поле.
> при rql запросе межно использовать ноду match она буедт восприниматься как Contains.

> Деталнее по поводу rql можно прочесть [тут](https://github.com/persvr/rql) и [тут](https://github.com/avz-cmf/zaboy-dojo/blob/master/doc/RQL.md)

##rollun/datastore/Rql/RqlParser
Объект RqlParser позволяет енкодировать и декодировать rql строку в query объект и обратно.  
Статический метод rqlDecode принимает на вход rql строку и возвращает Query объект.  
    Может принимать не rawurlencoded строку, но тогда спец-символы в строке должны быть екранированы.  
Статический метод rqlEncode принимает на вход Query объект и возвращает rql строку.

