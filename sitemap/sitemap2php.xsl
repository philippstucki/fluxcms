<?xml version="1.0"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
                              xmlns:map="http://apache.org/cocoon/sitemap/1.0"
>
    <xsl:output omit-xml-declaration="yes" indent="no" encoding="ISO-8859-1" method="xml"/>

    <xsl:template match="/map:sitemap"><xsl:processing-instruction name="php">
            <xsl:apply-templates />
    </xsl:processing-instruction></xsl:template>
    
    <xsl:template match="map:sitemap/map:pipelines">
        <xsl:apply-templates />
    </xsl:template>

    <xsl:template match="map:pipelines/map:pipeline/map:parameter[@name = 'expires']">    
        <xsl:if test="starts-with(@value,'access plus')">
            $this->setHeader("Expires",gmdate("r",strtotime("<xsl:value-of select="substring-after(@value,'access plus')"/>")));
        </xsl:if>

    </xsl:template>
    
    <xsl:template match="map:pipelines/map:pipeline">
        
        <xsl:if test="@benchmark">
            $this->benchmark["do"] = True;
        </xsl:if>
    
        <xsl:choose>
	   <xsl:when test="@cachable = 'yes' or @cachable = 'true'">
              $this->enableCaching(<xsl:call-template name="generateAttributes"/>);
           </xsl:when>
           <xsl:otherwise>
              $this->disableCaching();
           </xsl:otherwise>
        </xsl:choose>
        
        <xsl:apply-templates/>	
        
        if ($pipelineHit) {
        return true;
        }
        
    </xsl:template>

	<xsl:template match="map:pipeline//map:mount">
		$pipelineHit = $this->_mount(<xsl:call-template name="generateAttributes"/>);
	</xsl:template>		

	<xsl:template match="map:pipeline//map:aggregate">
	   	$aggregator = new popoon_components_aggregator($this);
		$aggregator->init(<xsl:call-template name="generateAttributes"/>);
		<xsl:apply-templates/>
		$aggregator->start($this->xml);
	</xsl:template>		
	
	<xsl:template match="map:aggregate/map:part">
		$aggregator->addPart(<xsl:call-template name="generateAttributes"/>);
	</xsl:template>		

	<xsl:template match="map:aggregate/map:part[map:generate]">
        $_xml = "";
        <xsl:apply-templates />
        $this->var2XMLObject($_xml);
		$aggregator->addPart(<xsl:call-template name="generateAttributes"/>,$_xml);
        unset($_xml);
	</xsl:template>		

	<xsl:template match="map:aggregate/map:part/map:generate" priority="100">
        <xsl:call-template name="doGenerate">
            <xsl:with-param name="xmlVar" select="'$_xml'"/>
        </xsl:call-template> /*do  Generate*/
	</xsl:template>		
	    
    <xsl:template match="map:pipeline//map:generate" name="doGenerate">
        <xsl:param name="xmlVar" select="'$this->xml'"/>
        <xsl:call-template name="setupComponent">
            <xsl:with-param name="prefix">generator</xsl:with-param>
            <xsl:with-param name="doParams">true</xsl:with-param>						
        </xsl:call-template>

                if($this->componentCache AND $this->componentCache->init($generator)){
                                                            
                    if(!$this->componentCache->isCached()){
                        /* miss! :( */
                        $generator->DomStart(<xsl:value-of select="$xmlVar"/>);          
                        $this->componentCache->store(<xsl:value-of select="$xmlVar"/>);
                    }
                    else{
                        /* hit! :)  */
                    }
                }
                else $generator->DomStart(<xsl:value-of select="$xmlVar"/>);
            
                $pipelineHit = True;
    </xsl:template>

    <xsl:template match="map:pipeline//map:read">
        <xsl:call-template name="setupComponent">
            <xsl:with-param name="prefix">reader</xsl:with-param>
        </xsl:call-template>

        if($this->componentCache AND $this->componentCache->init($reader)){

            if(!$componentCache->isCached()) {
                $this->componentCache->store(null, 'start', null, true);
            }
            else{
                print($componentCache->load());
            }
        
        }
        else{
            $reader->start();
        }
        		
        $pipelineHit = True;
        
    </xsl:template>

    
    <xsl:template match="map:pipeline//map:transform">
        <xsl:call-template name="setupComponent">
            <xsl:with-param name="prefix">transformer</xsl:with-param>			
            <xsl:with-param name="doParams">true</xsl:with-param>						
        </xsl:call-template>
        
         if($this->componentCache AND $this->componentCache->init($transformer)){
                if(!$this->componentCache->isCached()){
                        $this->componentCache->loadLast($this->xml);
                        $this->convertXML($transformer,$this->xml);
                        $transformer->DomStart($this->xml);
                        $this->componentCache->store($this->xml);                   
                 }
                   
                else {}
          }
         else{
                $this->convertXML($transformer, $this->xml);
                $transformer->DomStart($this->xml);
             }
        
        
    </xsl:template>
    
	<xsl:template match="map:act/map:parameter">
		<xsl:call-template  name="setParameter">
			<xsl:with-param name="prefix">action</xsl:with-param>
            <xsl:with-param name="doParams">true</xsl:with-param>						
		</xsl:call-template>
    </xsl:template>


    <xsl:template match="map:transform/map:parameter">
		<xsl:call-template  name="setParameter">
			<xsl:with-param name="prefix">transformer</xsl:with-param>
		</xsl:call-template>
    </xsl:template>

    <xsl:template match="map:generate/map:parameter">
		<xsl:call-template  name="setParameter">
			<xsl:with-param name="prefix">generator</xsl:with-param>
		</xsl:call-template>
    </xsl:template>

    <xsl:template match="map:serialize/map:parameter">
		<xsl:call-template  name="setParameter">
			<xsl:with-param name="prefix">serializer</xsl:with-param>
		</xsl:call-template>
    </xsl:template>

	<xsl:template match="map:pipeline//map:act">
        <xsl:call-template name="setupComponent">
            <xsl:with-param name="prefix">action</xsl:with-param>
            <xsl:with-param name="doParams">true</xsl:with-param>						
        </xsl:call-template>

        if ($map = $action->act())
        {
			$this->addMap($map);
		        <xsl:apply-templates select="*[name() != 'map:parameter']"/>		
			$this->removeMap();
        } 


	</xsl:template>
    
    <xsl:template match="map:pipeline//map:serialize">
        <xsl:call-template name="setupComponent">
            <xsl:with-param name="prefix">serializer</xsl:with-param>			
            <xsl:with-param name="doParams">true</xsl:with-param>						
        </xsl:call-template>

                if($this->componentCache AND $this->componentCache->init($serializer)){
    
                    if($this->componentCache->isCached()){
                        $this->componentCache->load(true);
                    }
                    else{
                        $this->componentCache->loadLast($this->xml);
                        $this->convertXML($serializer, $this->xml);
                        $this->componentCache->store(null, 'DomStart', $this->xml, true);
                        } 
                }

                else{
                   $this->convertXML($serializer, $this->xml);
                   $this->printHeader();
                   $serializer->DomStart($this->xml);
                }
        
    </xsl:template>
    
    <xsl:template match="map:match">
		<xsl:call-template name="setupComponent">
			<xsl:with-param name="prefix">matcher</xsl:with-param>
		</xsl:call-template>

        if ($matcher->match("<xsl:value-of select="@pattern"/>"))
        {
        <xsl:apply-templates/>
        } 
        // end of map match = <xsl:value-of select="@pattern"/>
        
        $this->removeMap();

    </xsl:template>
    
    <xsl:template match="map:select">
		<xsl:call-template name="setupComponent">
			<xsl:with-param name="prefix">selector</xsl:with-param>
			<xsl:with-param name="appendix"><xsl:value-of select="count(ancestor::*)"/></xsl:with-param>
		</xsl:call-template>

        <xsl:apply-templates select="map:when|map:otherwise"/>
		$selector<xsl:value-of select="count(ancestor::*)"/> = null;
    </xsl:template>
    
    <xsl:template match="map:select//map:when">
        
        <xsl:if test="position() > 1">
        else</xsl:if> if ($selector<xsl:value-of select="(count(ancestor::*)) - 1"/>->match('<xsl:value-of select="@test"/>')) {

        <xsl:apply-templates />
        } // end when test = <xsl:value-of select="@test"/>
    </xsl:template>
    
    <xsl:template match="map:select//map:otherwise">
        else {
        <xsl:apply-templates />
        } // end else
    </xsl:template>
    

    <xsl:template match="map:pipeline//map:redirect-to">
        $this->redirectTo('<xsl:value-of select="@uri"/>');
    </xsl:template>


	<xsl:template match="map:sitemap/map:components">
        <xsl:apply-templates />
	</xsl:template>
	
	<xsl:template match="map:components/map:schemes">
	        <xsl:apply-templates />
	</xsl:template>
	
	<xsl:template match="map:schemes/map:scheme">
		$this->_scheme(<xsl:call-template name="generateAttributes"/>);
	</xsl:template>
	
    <xsl:template match="map:action[@type='disableOutputCaching']">
        $this->disableOutputCaching();
    </xsl:template>
    
    <xsl:template name="generateAttributes">
        array(
        <xsl:for-each select="@*">"<xsl:value-of select="name()"/>"=>"<xsl:value-of select="."/>",</xsl:for-each>
        )
    </xsl:template>
    
    
    <xsl:template match="*">
        popoon::raiseError("<xsl:value-of select="name()"/> is not allowed as a child of <xsl:value-of select="name(..)"/> in your sitemap",POPOON_ERROR_WARNING);
        <xsl:apply-templates/>
    </xsl:template>   
    
    <xsl:template name="setupComponent">
        <xsl:param name="prefix"/>
        <xsl:param name="appendix"/>		
        <xsl:param name="convertXml">false</xsl:param>
	<xsl:param name="doParams">false</xsl:param>
        
        $<xsl:value-of select="$prefix"/><xsl:value-of select="$appendix"/> = new popoon_components_<xsl:value-of select="$prefix"/>s_<xsl:value-of select="@type"/>($this);
		
		<xsl:if test="$doParams != 'false'">
			<xsl:apply-templates select="map:parameter"/>
		</xsl:if>
        $<xsl:value-of select="$prefix"/><xsl:value-of select="$appendix"/>->init(<xsl:call-template name="generateAttributes"/>);
        <xsl:if test="$convertXml = 'true'">
            $this->convertXML($<xsl:value-of select="$prefix"/><xsl:value-of select="$appendix"/> ,$this->xml);
        </xsl:if>
        
    </xsl:template>
	
	

	<xsl:template name="setParameter">
		<xsl:param name="prefix"/>
        <xsl:variable name="type">
            <xsl:choose>
                <xsl:when test="string-length(@type) &gt; 0"><xsl:value-of select="@type"/></xsl:when>
                <xsl:otherwise>default</xsl:otherwise>
            </xsl:choose>
        </xsl:variable>
		$<xsl:value-of select="$prefix"/>->setParameter('<xsl:value-of select="$type"/>','<xsl:value-of select="@name"/>','<xsl:call-template name="escapeSingleQuotes"><xsl:with-param name="text" select="@value"/></xsl:call-template>');
		</xsl:template>
    
    <xsl:template name="escapeSingleQuotes">
        <xsl:param name="text"/>
        <xsl:call-template name="replace-string">
            <xsl:with-param name="text" select="$text"/>
            <xsl:with-param name="search">'</xsl:with-param>
            <xsl:with-param name="replace">\'</xsl:with-param>
        </xsl:call-template>
    </xsl:template>

    <xsl:template name="replace-string">
        <xsl:param name="text"/>
        <xsl:param name="search"/>
        <xsl:param name="replace"/>
        <xsl:choose>
            <xsl:when test="contains($text,$search)">
                <xsl:value-of select="substring-before($text,$search)"/>
                <xsl:value-of select="$replace"/>
                <xsl:call-template name="replace-string">
                    <xsl:with-param name="text"
                    select="substring-after($text,$search)"/>
                    <xsl:with-param name="search" select="$search"/>
                    <xsl:with-param name="replace" select="$replace"/>
                </xsl:call-template>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select="$text"/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    

</xsl:stylesheet>
