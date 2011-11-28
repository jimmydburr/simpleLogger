<?xml version="1.0" encoding="UTF-8"?>
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
    <FMPXMLRESULT>
        <xsl:copy-of select="$METADATA-FIELDS"/>
      </METADATA>
        <xsl:copy-of select="$RESULTSET-RECORDS"/>
      </RESULTSET>

  <xsl:template name="FIELD">

  <xsl:template name="ROW">
    <xsl:param name="RECORD-ID">generate-id(.)</xsl:param>
    <xsl:param name="MOD-ID"   >generate-id(.)</xsl:param>
    <xsl:param name="COLS"/>
    <ROW>
      <xsl:copy-of select="$COLS"/>
  </xsl:template>

  <xsl:template name="COL">
    <xsl:param name="DATA"/>
  </xsl:template>

</xsl:stylesheet>