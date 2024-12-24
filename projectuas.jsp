<%@ page session="true" contentType="text/html; charset=ISO-8859-1" %> <%@
taglib uri="http://www.tonbeller.com/jpivot" prefix="jp" %> <%@ taglib
prefix="c" uri="http://java.sun.com/jstl/core" %>

<jp:mondrianQuery
  id="query01"
  jdbcDriver="com.mysql.jdbc.Driver"
  jdbcUrl="jdbc:mysql://localhost/projectuas?user=root&password="
  catalogUri="/WEB-INF/queries/projectuas.xml"
>
  SELECT {[Measures].[Sales Amount], [Measures].[Quantity Sold]} ON COLUMNS,
  {([Time].[All Times], [Product].[All Products],[Customer].[All Customers],[Sales Territory].[All Territories], [Employee].[All Employees])} ON ROWS 
FROM [Sales]
</jp:mondrianQuery>

<c:set var="title01" scope="session">Query Sales using Mondrian OLAP</c:set>
