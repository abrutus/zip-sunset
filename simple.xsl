<html xsl:version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml">
    <body style="font-family:Arial;font-size:12pt;background-color:#EEEEEE">
        <div style="background-color:teal;color:white;padding:4px">
            <span style="font-weight:bold"><xsl:value-of select="xml/city"/></span>
            - <xsl:value-of select="xml/state"/>
        </div>
        <div style="margin-left:20px;margin-bottom:1em;font-size:10pt">
            <p>
                The next friday's sunset is at <xsl:value-of select="xml/sunset"/>.
                <span style="font-style:italic"> Happy Sabbath!  </span>
            </p>
            <p>
                This page is valid XML
            </p>
        </div>
    </body>
</html>
