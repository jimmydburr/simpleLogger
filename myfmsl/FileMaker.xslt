<?xml version="1.0" encoding="UTF-8"?><xsl:stylesheet version="1.0" 
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" 
  xmlns="http://www.filemaker.com/fmpxmlresult">
  
  <xsl:output method="xml"/> 

  <!-- FileMaker FMPXMLRESULT grammar -->

  <xsl:template name="TABLE">
    <xsl:param name="PRODUCT-BUILD"/>
    <xsl:param name="PRODUCT-NAME"/>
    <xsl:param name="PRODUCT-VERSION"/>
  
    <xsl:param name="DATABASE-NAME"/>
    <xsl:param name="DATABASE-LAYOUT"/>
    <xsl:param name="DATABASE-DATEFORMAT">M/d/yyyy</xsl:param>
    <xsl:param name="DATABASE-TIMEFORMAT">h:mm:ss a</xsl:param>
    <xsl:param name="DATABASE-RECORDS"/>
  
    <xsl:param name="METADATA-FIELDS"/>
    <xsl:param name="RESULTSET-FOUND"/>
    <xsl:param name="RESULTSET-RECORDS"/>
    <FMPXMLRESULT>      <xsl:attribute name="xmlns">http://www.filemaker.com/fmpxmlresult</xsl:attribute>      <ERRORCODE>0</ERRORCODE>      <PRODUCT>        <xsl:attribute name="BUILD"     ><xsl:value-of select="$PRODUCT-BUILD"      /></xsl:attribute>        <xsl:attribute name="NAME"      ><xsl:value-of select="$PRODUCT-NAME"       /></xsl:attribute>        <xsl:attribute name="VERSION"   ><xsl:value-of select="$PRODUCT-VERSION"    /></xsl:attribute>      </PRODUCT>      <DATABASE>        <xsl:attribute name="NAME"      ><xsl:value-of select="$DATABASE-NAME"      /></xsl:attribute>        <xsl:attribute name="LAYOUT"    ><xsl:value-of select="$DATABASE-LAYOUT"    /></xsl:attribute>        <xsl:attribute name="DATEFORMAT"><xsl:value-of select="$DATABASE-DATEFORMAT"/></xsl:attribute>        <xsl:attribute name="TIMEFORMAT"><xsl:value-of select="$DATABASE-TIMEFORMAT"/></xsl:attribute>        <xsl:attribute name="RECORDS"   ><xsl:value-of select="$DATABASE-RECORDS"   /></xsl:attribute>      </DATABASE>      <METADATA>
        <xsl:copy-of select="$METADATA-FIELDS"/>
      </METADATA>      <RESULTSET>        <xsl:attribute name="FOUND"><xsl:value-of select="$RESULTSET-FOUND"/></xsl:attribute>
        <xsl:copy-of select="$RESULTSET-RECORDS"/>
      </RESULTSET>    </FMPXMLRESULT>  </xsl:template>

  <xsl:template name="FIELD">    <xsl:param name="NAME">UNKNOWN</xsl:param>    <xsl:param name="TYPE">TEXT</xsl:param>    <xsl:param name="MAXREPEAT">1</xsl:param>    <xsl:param name="EMPTYOK">YES</xsl:param>    <FIELD>      <xsl:attribute name="NAME"     ><xsl:value-of select="$NAME"     /></xsl:attribute>      <xsl:attribute name="TYPE"     ><xsl:value-of select="$TYPE"     /></xsl:attribute>      <xsl:attribute name="MAXREPEAT"><xsl:value-of select="$MAXREPEAT"/></xsl:attribute>      <xsl:attribute name="EMPTYOK"  ><xsl:value-of select="$EMPTYOK"  /></xsl:attribute>    </FIELD>  </xsl:template>

  <xsl:template name="ROW">
    <xsl:param name="RECORD-ID">generate-id(.)</xsl:param>
    <xsl:param name="MOD-ID"   >generate-id(.)</xsl:param>
    <xsl:param name="COLS"/>
    <ROW>      <xsl:attribute name="RECORDID"><xsl:value-of select="$RECORD-ID"/></xsl:attribute>      <xsl:attribute name="MODID"   ><xsl:value-of select="$MOD-ID"   /></xsl:attribute>
      <xsl:copy-of select="$COLS"/>    </ROW>
  </xsl:template>

  <xsl:template name="COL">
    <xsl:param name="DATA"/>    <COL><DATA><xsl:value-of select="$DATA"/></DATA></COL>
  </xsl:template>

</xsl:stylesheet>