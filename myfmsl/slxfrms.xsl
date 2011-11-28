<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="2.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fmp="http://www.filemaker.com/fmpxmlresult" exclude-result-prefixes="fmp">
<xsl:output method="text" omit-xml-declaration="yes" media-type="text/plain" encoding="UTF-8"/>
<xsl:template match="fmp:FMPXMLRESULT">
This data was exported from N5SPE's Simple Logger v 1.0 , conforming to ADIF 
standard specification version 2.22.
&lt;EOH&gt;
	<xsl:for-each select="fmp:RESULTSET/fmp:ROW">
		<xsl:for-each select="fmp:COL[1]"> 
			&lt;QSO_DATE:<xsl:value-of select="string-length()"/>:D&gt;<xsl:value-of select="."/>
		</xsl:for-each>
		<xsl:for-each select="fmp:COL[2]"> 
			&lt;TIME_ON:<xsl:value-of select="string-length()"/>&gt;<xsl:value-of select="."/>
		</xsl:for-each>
		<xsl:for-each select="fmp:COL[3]"> 
			&lt;CALL:<xsl:value-of select="string-length()"/>&gt;<xsl:value-of select="fmp:DATA"/>
		</xsl:for-each>
		<xsl:for-each select="fmp:COL[4]"> 
			&lt;FREQ:<xsl:value-of select="string-length()"/>&gt;<xsl:value-of select="fmp:DATA"/>
		</xsl:for-each>
		<xsl:for-each select="fmp:COL[5]"> 
			&lt;BAND:<xsl:value-of select="string-length()"/>&gt;<xsl:value-of select="fmp:DATA"/>
		</xsl:for-each>
		<xsl:for-each select="fmp:COL[6]"> 
			&lt;RST_Sent:<xsl:value-of select="string-length()"/>&gt;<xsl:value-of select="fmp:DATA"/>
		</xsl:for-each>
		<xsl:for-each select="fmp:COL[7]"> 
			&lt;RST_RCVD:<xsl:value-of select="string-length()"/>&gt;<xsl:value-of select="fmp:DATA"/>
		</xsl:for-each>
		<xsl:for-each select="fmp:COL[8]"> 
			&lt;MODE:<xsl:value-of select="string-length()"/>&gt;<xsl:value-of select="fmp:DATA"/>
		</xsl:for-each>
		<xsl:for-each select="fmp:COL[9]"> 
			&lt;Power:<xsl:value-of select="string-length()"/>&gt;<xsl:value-of select="fmp:DATA"/>
		</xsl:for-each>
		&lt;EOR&gt;
	</xsl:for-each>
</xsl:template>
</xsl:stylesheet>