## 1.distinct 的使用(与distinctrow相同)

distinct一般是用来去除查询结果中的重复记录的，而且这个语句在select、insert、delete和update中只可以在select中使用。

```sql
select distinct expression[,expression...] from tables [where conditions];
```
#### 1.1 对单列操作
```sql
select distinct country from person;
```
#### 1.2 对多列操作

```sql
select distinct country, province from person
```

当distinct应用到多个字段的时候，其应用的范围是其后面的所有字段，而不只是紧挨着它的一个字段，而且distinct只能放到所有字段的前面。如下语句错误：

```sql
SELECT country, distinct province from person; // 该语句是错误的
```
可以对*使用distinct

```sql
select DISTINCT * from person ;//同于：select DISTINCT id, `name`, country, province, city from person;
```

#### 1.3 针对NULL的处理

distinct对NULL是不进行过滤的，即返回的结果中是包含NULL值的。

#### 1.4 与ALL不能同时使用

默认情况下，查询时返回所有的结果，此时使用的就是all语句，这是与distinct相对应的，如下：

```sql
select all country, province from person;
```

## 2.count

#### 2.1 count(1) and count(*)

从执行计划来看，count(1)和count(*)的效果是一样的。当表的数据量大些时，对表作分析之后，使用count(1)还要比使用count(*)用时多！但是当数据
量小（1W以内），当表做过分析之后，count(1)会比count(*)的用时少些，不过差不了多少。如果count(1)是聚索引，id，那肯定是count(1)快，但是差别很小。
因为count(*)会自动优化指定到哪一个字段。所以没必要去count(1)，用count(*)，sql会帮你完成优化的，因此：count(1)和count(*)基本没有差别！

#### 2.2 count(1) and count(字段)

count(1) 会统计表中的所有的记录数，包含字段为null 的记录。
count(字段) 会统计该字段在表中出现的次数，忽略字段为null 的情况。即不统计字段为null 的记录。

#### 2.3 count(*) 和 count(1)和count(列名)区别

执行效果上：

```text
count(*)包括了所有的列，相当于行数，在统计结果的时候，不会忽略列值为NULL。
count(1)包括了忽略列值为NULL的所有列，用1代表代码行，在统计结果的时候，不会忽略列值为NULL。
count(列名)只包括列名那一列，在统计结果的时候，会忽略列值为空（这里的空不是只空字符串或者0，而是表示null）的计数，即某个字段值为NULL时，不统计。
```
执行效率上：

```text
列名为主键，count(列名)会比count(1)快。
列名不为主键，count(1)会比count(列名)快。
如果表多个列并且没有主键，则 count（1） 的执行效率优于 count（*）。
如果有主键，则 select count（主键）的执行效率是最优的。
如果表只有一个字段，则 select count（*）最优。
```

## 3.ANY 和 ALL

ANY:表示任何一个就行了,如;数组A中的值比数组B中任何一个都要大,那么只要A和B中最小的比较就行了.
ALL:表示所有都要比较,如:数组A中的值比数组B中所有的数都要大,那么A要和B中最大的值比较才行.

例子：
```sql
//查询选修编号为"3-105"且成绩高于选修编号为"3-245"课程的同学c_no.s_no和sc_degree
SELECT * FROM score WHERE sc_degree > ALL (select sc_degree from score WHERE c_no = '3-245') AND c_no = '3-105';
//查询选修编号为"3-105"课程且成绩至少高于选修编号为'3-245'同学的c_no,s_no和sc_degree,并且按照sc_degree从高到地次序排序
select * from score where c_no = '3-105' AND sc_degree > ANY(SELECT sc_degree FROM score WHERE c_no = '3-245' ) ORDER BY sc_degree desc ;

```
