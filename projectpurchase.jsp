<%@ page session="true" contentType="text/html; charset=ISO-8859-1" %> <%@
taglib uri="http://www.tonbeller.com/jpivot" prefix="jp" %> <%@ taglib
prefix="c" uri="http://java.sun.com/jstl/core" %>

<jp:mondrianQuery
  id="query01"
  jdbcDriver="com.mysql.jdbc.Driver"
  jdbcUrl="jdbc:mysql://localhost/projectuas?user=root&password="
  catalogUri="/WEB-INF/queries/projectpurchase.xml"
>
  SELECT {[Measures].[Amount], [Measures].[Quantity]} ON COLUMNS, {([Time].[All Times], [Product].[All Products],[Vendor].[All Vendors],[ShipMethod].[All ShipMethod])} ON ROWS FROM [Purchases]
</jp:mondrianQuery>

<c:set var="title01" scope="session"
  >Query Purchases using Mondrian OLAP</c:set
>
