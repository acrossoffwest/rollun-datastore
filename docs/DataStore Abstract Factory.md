#Защита о циклических зависимостей

AbstractDataStoreFactory имеет защитный механизм от циклических зависимостей.
Пример:

Допустим у нас есть две фабрики для создания DataStore которыйе во время создания
обращается к SM за каким то сервисом.
В таком случае если один из них не сможет найти свой сервис, они попадут в цеклическую зависимость друг на друга. 
От таких случаев защещает данный метод.

С помощью статических переменных $KEY_IN_CREATE $KEY_IN_CANCREATE - флаги
мы должны управлять состоянием создания нашего DS.

В методе __invoke() нашего наследника мы должны проверить наличие флага $KEY_IN_CREATE
и в случае если он установлен, мы обязаны выбросить исключение.
Иначе мы дорлжны установить даный флаг, и обнулить его в случае выхода из функции.

##В случае если вы хотите переопрделеить функцию canCreate()
В начале функции нужно проверить состоянии флагов $KEY_IN_CREATE $KEY_IN_CANCREATE, и в случае если хоть один из них установенл мы должны вернуть false
иначе мы устанавливаем флаг $KEY_IN_CANCREATE, при выходе из функции мы обязаны его убрать.