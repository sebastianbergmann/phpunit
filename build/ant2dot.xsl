<?xml version="1.0"?>

<!-- 

	License for $Id: ant2dot.xsl,v 1.32 2007/01/02 01:25:03 pvandenberk Exp $

	copyright (c) 2003 by Peter Vandenberk, pvandenberk@users.sourceforge.net

	Permission to use, copy, modify, and distribute this software and its
	documentation  under the terms of  the GNU General Public License  is 
	hereby granted.  No representations are made about the suitability of 
	this software for any purpose. It is provided "as is" without express 
	or implied warranty. 

	See the GNU General Public License for more details. 

	Documents produced by ant2dot.xsl are derivative works derived from 
	the input used in their production;   they are not affected by this 
	license.

-->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">

	<!-- specify the XSLT output settings -->

	<xsl:output method="text" indent="no" encoding="iso-8859-1"/>
	<!-- TODO: what are the other output attributes??? -->

	<!-- global graph parameter(s) -->

	<xsl:param name="ant.file">__UNDEF__</xsl:param>
	<xsl:param name="graph.label">__UNDEF__</xsl:param>
	<xsl:param name="graph.rankdir">LR</xsl:param><!-- "TB" -->

	<!-- optional graph parameter(s) -->

	<xsl:param name="create.project.node">__UNDEF__</xsl:param>
	<xsl:param name="use.target.conditions">__UNDEF__</xsl:param>
	<xsl:param name="use.target.description">__UNDEF__</xsl:param>

	<!-- optional color parameter(s) -->

	<xsl:param name="project.node.fill.color">#CCCCCC</xsl:param>
	<xsl:param name="project.node.font.color">#000000</xsl:param>

	<xsl:param name="local.node.fill.color">#FFFFFF</xsl:param>
	<xsl:param name="local.node.font.color">#000000</xsl:param>

	<xsl:param name="foreign.node.fill.color">#AAAAAA</xsl:param>
	<xsl:param name="foreign.node.font.color">#000000</xsl:param>

	<xsl:param name="subgraph.fill.color">#FFFFFF</xsl:param>

	<!-- currently unsupported parameter(s) -->

	<xsl:param name="use.project.description">__UNDEF__</xsl:param>
	<xsl:param name="mark.internal.targets">__UNDEF__</xsl:param>
	<xsl:param name="suppress.ant.tasks">__UNDEF__</xsl:param>
	<xsl:param name="suppress.antcall.tasks">__UNDEF__</xsl:param>
	<xsl:param name="rank.by.target.id">__UNDEF__</xsl:param>

	<!-- global variables, incl. automatic CVS substituted keywords -->

	<xsl:variable name="ant.file.url" 
		select="concat('file://', translate($ant.file, '\\', '/'))"/>

	<xsl:variable name="ant2dot.xsl.name" 
		select="translate('$Name:  $', '_', '.')"/>

	<xsl:variable name="ant2dot.xsl.id" 
		select="'$Id: ant2dot.xsl,v 1.32 2007/01/02 01:25:03 pvandenberk Exp $'"/>

	<!-- matched templates -->
	<!-- (these templates don't and shouldn't generate DOT output) -->

	<xsl:template match="/">

		<!-- STEP 1: generate the DOT header -->
		<xsl:call-template name="create-dot-header">
			<xsl:with-param name="dot.graph.label" select="$graph.label"/>
			<xsl:with-param name="dot.graph.rankdir" select="$graph.rankdir"/>
		</xsl:call-template>

		<!-- STEP 2: generate the DOT graph for the ANT project -->
		<xsl:apply-templates select="project"/>

		<!-- STEP 3: generate the DOT footer -->
		<xsl:call-template name="create-dot-footer">
			<xsl:with-param name="dot.graph.label" select="$graph.label"/>
		</xsl:call-template>

	</xsl:template>
	
	<xsl:template match="/project">

		<!-- STEP 0: generate the "project" node -->
		<xsl:if test="$create.project.node != '__UNDEF__'">
			<xsl:call-template name="create-project-node">
				<xsl:with-param name="file.url" select="$ant.file.url"/>
				<xsl:with-param name="project.name" select="@name"/>
				<xsl:with-param name="project.default" select="@default"/>
				<xsl:with-param name="project.basedir" select="@basedir"/>
			</xsl:call-template>
		</xsl:if>

		<!-- STEP 1: generate the "target" nodes -->
		<xsl:apply-templates select="target" mode="node">
			<xsl:with-param name="default.target" select="@default"/>
		</xsl:apply-templates>

		<!-- STEP 2: generate the edges for "target" dependencies -->
		<xsl:apply-templates select="target" mode="edges"/>

		<!-- STEP 3: generate the "antcall" task nodes -->
		<xsl:apply-templates select="target/antcall" mode="node">
			<xsl:with-param name="default.target" select="@default"/>
		</xsl:apply-templates>

		<!-- STEP 4: generate the edges for "antcall" dependencies -->
		<xsl:apply-templates select="target/antcall" mode="edges"/>

		<!-- STEP 5: generate the "ant" task nodes -->
		<xsl:apply-templates select="target/ant" mode="node">
			<xsl:with-param name="default.target" select="@default"/>
		</xsl:apply-templates>

		<!-- STEP 6: generate the edges for "ant" dependencies -->
		<xsl:apply-templates select="target/ant" mode="edges"/>

		<!-- STEP 7: rank the "ant" task nodes -->
<!--
		<xsl:call-template name="create-rank-header"/>
			<xsl:apply-templates select="target/ant" mode="rank"/>
		<xsl:call-template name="create-rank-footer"/>
-->

	</xsl:template>

	<xsl:template match="target" mode="node">
		<xsl:param name="default.target">__UNDEF__</xsl:param>
		<xsl:choose>
			<xsl:when test="$use.target.description != '__UNDEF__'">
				<xsl:choose>
					<xsl:when test="$use.target.conditions != '__UNDEF__'">
						<xsl:call-template name="create-local-node">
							<xsl:with-param name="default.name" select="$default.target"/>
							<xsl:with-param name="node.name" select="@name"/>
							<xsl:with-param name="node.description" select="@description"/>
							<xsl:with-param name="node.if" select="@if"/>
							<xsl:with-param name="node.unless" select="@unless"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="create-local-node">
							<xsl:with-param name="default.name" select="$default.target"/>
							<xsl:with-param name="node.name" select="@name"/>
							<xsl:with-param name="node.description" select="@description"/>
						</xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:when>
			<xsl:otherwise>
				<xsl:choose>
					<xsl:when test="$use.target.conditions != '__UNDEF__'">
						<xsl:call-template name="create-local-node">
							<xsl:with-param name="default.name" select="$default.target"/>
							<xsl:with-param name="node.name" select="@name"/>
							<xsl:with-param name="node.if" select="@if"/>
							<xsl:with-param name="node.unless" select="@unless"/>
						</xsl:call-template>
					</xsl:when>
					<xsl:otherwise>
						<xsl:call-template name="create-local-node">
							<xsl:with-param name="default.name" select="$default.target"/>
							<xsl:with-param name="node.name" select="@name"/>
						</xsl:call-template>
					</xsl:otherwise>
				</xsl:choose>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="target" mode="edges">
		<xsl:choose>
			<xsl:when test="contains(@depends, ',')">
				<xsl:call-template name="create-depends-edges">
					<xsl:with-param name="node.name" select="@name"/>
					<xsl:with-param name="node.depends" select="@depends"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-depends-edge">
					<xsl:with-param name="edge.from" select="@name"/>
					<xsl:with-param name="edge.to" select="@depends"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="antcall" mode="node">
		<xsl:param name="default.target">__UNDEF__</xsl:param>
		<xsl:variable name="antcall.target"><xsl:value-of select="@target"/></xsl:variable>
		<xsl:if test="count(/project/target[@name=$antcall.target]) = 0">
			<!-- the above test avoids node duplication in the generated file -->
			<!-- thanks to Johannes Schaback for finding & reporting this bug -->
			<xsl:call-template name="create-local-node">
				<xsl:with-param name="default.name" select="$default.target"/>
				<xsl:with-param name="node.name" select="$antcall.target"/>
			</xsl:call-template>
		</xsl:if>
	</xsl:template>

	<xsl:template match="antcall" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../antcall) > 1">
				<xsl:call-template name="create-antcall-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="@target"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-antcall-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="@target"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- ugly bit of XSL/XPATH hacking: fill in default values if not specified in ANT script -->

	<xsl:template match="ant" mode="node">
		<xsl:call-template name="create-foreign-node">
			<xsl:with-param name="dir.name" select="'${basedir}'"/>
			<xsl:with-param name="dir.escaped" select="'$\\{basedir\\}'"/>
			<xsl:with-param name="antfile.name" select="'build.xml'"/>
			<xsl:with-param name="antfile.escaped" select="'build.xml'"/>
			<xsl:with-param name="node.name" select="'${default.target}'"/>
			<xsl:with-param name="node.escaped" select="'$\\{default.target\\}'"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="ant[@target != '']" mode="node">
		<xsl:call-template name="escape-target-name">
			<xsl:with-param name="dir.name" select="'${basedir}'"/>
			<xsl:with-param name="dir.escaped" select="'$\\{basedir\\}'"/>
			<xsl:with-param name="antfile.name" select="'build.xml'"/>
			<xsl:with-param name="antfile.escaped" select="'build.xml'"/>
			<xsl:with-param name="node.name" select="@target"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="ant[@antfile != '']" mode="node">
		<xsl:call-template name="escape-antfile-name">
			<xsl:with-param name="dir.name" select="'${basedir}'"/>
			<xsl:with-param name="dir.escaped" select="'$\\{basedir\\}'"/>
			<xsl:with-param name="antfile.name" select="@antfile"/>
			<xsl:with-param name="node.name" select="'${default.target}'"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="ant[@dir != '']" mode="node">
		<xsl:call-template name="escape-dir-name">
			<xsl:with-param name="dir.name" select="@dir"/>
			<xsl:with-param name="antfile.name" select="'build.xml'"/>
			<xsl:with-param name="node.name" select="'${default.target}'"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="ant[@antfile != '' and @target != '']" mode="node">
		<xsl:call-template name="escape-antfile-name">
			<xsl:with-param name="dir.name" select="'${basedir}'"/>
			<xsl:with-param name="dir.escaped" select="'$\\{basedir\\}'"/>
			<xsl:with-param name="antfile.name" select="@antfile"/>
			<xsl:with-param name="node.name" select="@target"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="ant[@dir != '' and @target != '']" mode="node">
		<xsl:call-template name="escape-dir-name">
			<xsl:with-param name="dir.name" select="@dir"/>
			<xsl:with-param name="antfile.name" select="'build.xml'"/>
			<xsl:with-param name="node.name" select="@target"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="ant[@dir != '' and @antfile != '']" mode="node">
		<xsl:call-template name="escape-dir-name">
			<xsl:with-param name="dir.name" select="@dir"/>
			<xsl:with-param name="antfile.name" select="@antfile"/>
			<xsl:with-param name="node.name" select="'${default.target}'"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="ant[@dir != '' and @antfile != '' and @target != '']" mode="node">
		<xsl:call-template name="escape-dir-name">
			<xsl:with-param name="dir.name" select="@dir"/>
			<xsl:with-param name="antfile.name" select="@antfile"/>
			<xsl:with-param name="node.name" select="@target"/>
		</xsl:call-template>
	</xsl:template>

	<!-- ugly bit of XSL/XPATH hacking: fill in default values if not specified in ANT script -->

	<xsl:template match="ant" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../ant) > 1">
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space('${basedir}'), '_', 
						normalize-space('build.xml'), '_', 
						normalize-space('${default.target}')
					)"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space('${basedir}'), '_',
						normalize-space('build.xml'), '_', 
						normalize-space('${default.target}')
					)"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="ant[@target != '']" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../ant) > 1">
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space('${basedir}'), '_', 
						normalize-space('build.xml'), '_', 
						normalize-space(@target)
					)"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space('${basedir}'), '_',
						normalize-space('build.xml'), '_', 
						normalize-space(@target)
					)"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="ant[@antfile != '']" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../ant) > 1">
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space('${basedir}'), '_', 
						normalize-space(@antfile), '_', 
						normalize-space('${default.target}')
					)"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space('${basedir}'), '_',
						normalize-space(@antfile), '_', 
						normalize-space('${default.target}')
					)"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="ant[@dir != '']" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../ant) > 1">
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space(@dir), '_', 
						normalize-space('build.xml'), '_', 
						normalize-space('${default.target}')
					)"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space(@dir), '_',
						normalize-space('build.xml'), '_', 
						normalize-space('${default.target}')
					)"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="ant[@antfile != '' and @target != '']" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../ant) > 1">
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space('${basedir}'), '_', 
						normalize-space(@antfile), '_', 
						normalize-space(@target)
					)"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space('${basedir}'), '_',
						normalize-space(@antfile), '_', 
						normalize-space(@target)
					)"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="ant[@dir != '' and @target != '']" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../ant) > 1">
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space(@dir), '_', 
						normalize-space('build.xml'), '_', 
						normalize-space(@target)
					)"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space(@dir), '_',
						normalize-space('build.xml'), '_', 
						normalize-space(@target)
					)"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="ant[@dir != '' and @antfile != '']" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../ant) > 1">
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space(@dir), '_', 
						normalize-space(@antfile), '_', 
						normalize-space('${default.target}')
					)"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space(@dir), '_',
						normalize-space(@antfile), '_', 
						normalize-space('${default.target}')
					)"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="ant[@dir != '' and @antfile != '' and @target != '']" mode="edges">
		<xsl:choose>
			<xsl:when test="count(../ant) > 1">
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space(@dir), '_', 
						normalize-space(@antfile), '_', 
						normalize-space(@target)
					)"/>
					<xsl:with-param name="edge.index" select="position()"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-ant-edge">
					<xsl:with-param name="edge.from" select="../@name"/>
					<xsl:with-param name="edge.to" select="concat(
						normalize-space(@dir), '_',
						normalize-space(@antfile), '_', 
						normalize-space(@target)
					)"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<!-- ugly bit of XSL/XPATH hacking: fill in default values if not specified in ANT script -->

	<xsl:template match="ant" mode="rank">
		<xsl:call-template name="create-node-rank">
			<xsl:with-param name="dir.name" select="'${basedir}'"/>
			<xsl:with-param name="antfile.name" select="@antfile"/>
			<xsl:with-param name="node.name" select="@target"/>
		</xsl:call-template>
	</xsl:template>

	<!-- named templates -->
	<!-- (these - and only these - templates generate DOT output) -->

	<xsl:template name="create-dot-header">
		<xsl:param name="dot.graph.rankdir">LR</xsl:param>
		<xsl:param name="dot.graph.label">__UNDEF__</xsl:param>
strict digraph "<xsl:value-of select="$dot.graph.label"/>" {

	graph [
		fontsize=10,
		fontcolor=black,
		fontname=Courier,
		rankdir = <xsl:value-of select="$dot.graph.rankdir"/>
	];
	
	node [
		fontsize=8,
		fontcolor=black,
		fontname=Courier,
		shape=box,
		color=black,
		style="bold, filled"
	];
	
	edge [
		fontsize=6,
		fontcolor=black,
		fontname=Courier
	];

		<xsl:if test="$dot.graph.label != '__UNDEF__'">
	subgraph cluster_project {

		style=filled;
		fillcolor="<xsl:value-of select="$subgraph.fill.color"/>";

		label="<xsl:value-of select="$dot.graph.label"/>";
		labelloc=b;
		labeljust=r;

		</xsl:if>
	</xsl:template>

	<xsl:template name="create-project-node">
		<xsl:param name="file.url">__UNDEF__</xsl:param>
		<xsl:param name="project.name">__UNDEF__</xsl:param>
		<xsl:param name="project.default">__UNDEF__</xsl:param>
		<xsl:param name="project.basedir">__UNDEF__</xsl:param>
		"project" [
			style="filled",
			label="<xsl:value-of select="concat(
				$file.url, '|',
				'name=\&quot;', $project.name, '\&quot;', '|',
				'{', 
						'default=\&quot;', $project.default, '\&quot;', '|',
						'basedir=\&quot;', $project.basedir, '\&quot;', 
				'}'
			)"/>",
			fillcolor="<xsl:value-of select="$project.node.fill.color"/>",
			fontcolor="<xsl:value-of select="$project.node.font.color"/>",
			shape=record
		];
	
	</xsl:template>
	
	<xsl:template name="create-local-node">
		<xsl:param name="default.name">__UNDEF__</xsl:param>
		<xsl:param name="node.name">__UNDEF__</xsl:param>
		<xsl:param name="node.if">__UNDEF__</xsl:param>
		<xsl:param name="node.unless">__UNDEF__</xsl:param>
		<xsl:param name="node.description">__UNDEF__</xsl:param>
		<xsl:if test="$node.name != '__UNDEF__'">
			"<xsl:value-of select="normalize-space($node.name)"/>" [
				<xsl:choose>
					<xsl:when test="
						$node.description != '__UNDEF__' 
						and $node.description != ''
					">
						<xsl:choose>
							<xsl:when test="
								$node.if != '__UNDEF__' 
								and $node.if != ''
							">
								<xsl:choose>
									<xsl:when test="
										$node.unless != '__UNDEF__' 
										and $node.unless != ''
									">
										<!-- DESC, IF, UNLESS -->
										label="<xsl:value-of select="concat(
												normalize-space($node.name), '|', 
												'[DESC] ', $node.description, '|',
												'[IF] ', $node.if, '|',
												'[UNLESS] ', $node.unless
										)"/>" 
										, shape=record
									</xsl:when>
									<xsl:otherwise>
										<!-- DESC, IF, !UNLESS -->
										label="<xsl:value-of select="concat(
												normalize-space($node.name), '|', 
												'[DESC] ', $node.description, '|',
												'[IF] ', $node.if
										)"/>" 
										, shape=record
									</xsl:otherwise>
								</xsl:choose>
							</xsl:when>
							<xsl:otherwise>
								<xsl:choose>
									<xsl:when test="
										$node.unless != '__UNDEF__' 
										and $node.unless != ''
									">
										<!-- DESC, !IF, UNLESS -->
										label="<xsl:value-of select="concat(
												normalize-space($node.name), '|', 
												'[DESC] ', $node.description, '|',
												'[UNLESS] ', $node.unless
										)"/>" 
										, shape=record
									</xsl:when>
									<xsl:otherwise>
										<!-- DESC, !IF, !UNLESS -->
										label="<xsl:value-of select="concat(
												normalize-space($node.name), '|', 
												'[DESC] ', $node.description
										)"/>" 
										, shape=record
									</xsl:otherwise>
								</xsl:choose>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<xsl:choose>
							<xsl:when test="
								$node.if != '__UNDEF__' 
								and $node.if != ''
							">
								<xsl:choose>
									<xsl:when test="
										$node.unless != '__UNDEF__' 
										and $node.unless != ''
									">
										<!-- !DESC, IF, UNLESS -->
										label="<xsl:value-of select="concat(
												normalize-space($node.name), '|', 
												'[IF] ', $node.if, '|',
												'[UNLESS] ', $node.unless
										)"/>" 
										, shape=record
									</xsl:when>
									<xsl:otherwise>
										<!-- !DESC, IF, !UNLESS -->
										label="<xsl:value-of select="concat(
												normalize-space($node.name), '|', 
												'[IF] ', $node.if
										)"/>" 
										, shape=record
									</xsl:otherwise>
								</xsl:choose>
							</xsl:when>
							<xsl:otherwise>
								<xsl:choose>
									<xsl:when test="
										$node.unless != '__UNDEF__' 
										and $node.unless != ''
									">
										<!-- !DESC, !IF, UNLESS -->
										label="<xsl:value-of select="concat(
												normalize-space($node.name), '|', 
												'[UNLESS] ', $node.unless
										)"/>" 
										, shape=record
									</xsl:when>
									<xsl:otherwise>
										<!-- !DESC, !IF, !UNLESS -->
										label="<xsl:value-of select="normalize-space($node.name)"/>" 
									</xsl:otherwise>
								</xsl:choose>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:otherwise>
				</xsl:choose>
				<xsl:if test="$node.name = $default.name">
					, style="dashed, bold, filled"
				</xsl:if>
				, fillcolor="<xsl:value-of select="$local.node.fill.color"/>"
				, fontcolor="<xsl:value-of select="$local.node.font.color"/>"
			];
		</xsl:if>
	</xsl:template>

	<!-- ugly bit of XSL/XPATH hacking: escape curly braces in the parameters -->

	<xsl:template name="escape-dir-name">
		<xsl:param name="dir.name">__UNDEF__</xsl:param>
		<xsl:param name="antfile.name">__UNDEF__</xsl:param>
		<xsl:param name="node.name">__UNDEF__</xsl:param>
		<xsl:choose>
			<xsl:when test="contains($dir.name, '{') and contains($dir.name, '}')">
				<xsl:variable name="dir.partially.escaped">
					<xsl:value-of select="concat(
						substring-before($dir.name, '{'),
						'\\{',
						substring-after($dir.name, '{')
					)"/>
				</xsl:variable>
				<xsl:variable name="dir.escaped">
					<xsl:value-of select="concat(
						substring-before($dir.partially.escaped, '}'),
						'\\}',
						substring-after($dir.partially.escaped, '}')
					)"/>
				</xsl:variable>
				<xsl:call-template name="escape-antfile-name">
					<xsl:with-param name="dir.name" select="$dir.name"/>
					<xsl:with-param name="dir.escaped" select="$dir.escaped"/>
					<xsl:with-param name="antfile.name" select="$antfile.name"/>
					<xsl:with-param name="node.name" select="$node.name"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="escape-antfile-name">
					<xsl:with-param name="dir.name" select="$dir.name"/>
					<xsl:with-param name="dir.escaped" select="$dir.name"/>
					<xsl:with-param name="antfile.name" select="$antfile.name"/>
					<xsl:with-param name="node.name" select="$node.name"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="escape-antfile-name">
		<xsl:param name="dir.name">__UNDEF__</xsl:param>
		<xsl:param name="dir.escaped">__UNDEF__</xsl:param>
		<xsl:param name="antfile.name">__UNDEF__</xsl:param>
		<xsl:param name="node.name">__UNDEF__</xsl:param>
		<xsl:choose>
			<xsl:when test="contains($antfile.name, '{') and contains($antfile.name, '}')">
				<xsl:variable name="antfile.partially.escaped">
					<xsl:value-of select="concat(
						substring-before($antfile.name, '{'),
						'\\{',
						substring-after($antfile.name, '{')
					)"/>
				</xsl:variable>
				<xsl:variable name="antfile.escaped">
					<xsl:value-of select="concat(
						substring-before($antfile.partially.escaped, '}'),
						'\\}',
						substring-after($antfile.partially.escaped, '}')
					)"/>
				</xsl:variable>
				<xsl:call-template name="escape-target-name">
					<xsl:with-param name="dir.name" select="$dir.name"/>
					<xsl:with-param name="dir.escaped" select="$dir.escaped"/>
					<xsl:with-param name="antfile.name" select="$antfile.name"/>
					<xsl:with-param name="antfile.escaped" select="$antfile.escaped"/>
					<xsl:with-param name="node.name" select="$node.name"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="escape-target-name">
					<xsl:with-param name="dir.name" select="$dir.name"/>
					<xsl:with-param name="dir.escaped" select="$dir.escaped"/>
					<xsl:with-param name="antfile.name" select="$antfile.name"/>
					<xsl:with-param name="antfile.escaped" select="$antfile.name"/>
					<xsl:with-param name="node.name" select="$node.name"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="escape-target-name">
		<xsl:param name="dir.name">__UNDEF__</xsl:param>
		<xsl:param name="dir.escaped">__UNDEF__</xsl:param>
		<xsl:param name="antfile.name">__UNDEF__</xsl:param>
		<xsl:param name="antfile.escaped">__UNDEF__</xsl:param>
		<xsl:param name="node.name">__UNDEF__</xsl:param>
		<xsl:choose>
			<xsl:when test="contains($node.name, '{') and contains($node.name, '}')">
				<xsl:variable name="node.partially.escaped">
					<xsl:value-of select="concat(
						substring-before($node.name, '{'),
						'\\{',
						substring-after($node.name, '{')
					)"/>
				</xsl:variable>
				<xsl:variable name="node.escaped">
					<xsl:value-of select="concat(
						substring-before($node.partially.escaped, '}'),
						'\\}',
						substring-after($node.partially.escaped, '}')
					)"/>
				</xsl:variable>
				<xsl:call-template name="create-foreign-node">
					<xsl:with-param name="dir.name" select="$dir.name"/>
					<xsl:with-param name="dir.escaped" select="$dir.escaped"/>
					<xsl:with-param name="antfile.name" select="$antfile.name"/>
					<xsl:with-param name="antfile.escaped" select="$antfile.escaped"/>
					<xsl:with-param name="node.name" select="$node.name"/>
					<xsl:with-param name="node.escaped" select="$node.escaped"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-foreign-node">
					<xsl:with-param name="dir.name" select="$dir.name"/>
					<xsl:with-param name="dir.escaped" select="$dir.escaped"/>
					<xsl:with-param name="antfile.name" select="$antfile.name"/>
					<xsl:with-param name="antfile.escaped" select="$antfile.escaped"/>
					<xsl:with-param name="node.name" select="$node.name"/>
					<xsl:with-param name="node.escaped" select="$node.name"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="create-foreign-node">
		<xsl:param name="dir.name">__UNDEF__</xsl:param>
		<xsl:param name="dir.escaped">__UNDEF__</xsl:param>
		<xsl:param name="antfile.name">__UNDEF__</xsl:param>
		<xsl:param name="antfile.escaped">__UNDEF__</xsl:param>
		<xsl:param name="node.name">__UNDEF__</xsl:param>
		<xsl:param name="node.escaped">__UNDEF__</xsl:param>
		<xsl:if test="$dir.name != '__UNDEF__'">
			<xsl:if test="$antfile.name != '__UNDEF__'">
				<xsl:if test="$node.name != '__UNDEF__'">
					"<xsl:value-of select="concat(
						normalize-space($dir.name), '_',
						normalize-space($antfile.name), '_',
						normalize-space($node.name)
					)"/>" [
						label="<xsl:value-of select="concat(
							normalize-space($node.escaped), '|',
							normalize-space($dir.escaped), '/',
							normalize-space($antfile.escaped)
						)"/>", 
						fillcolor="<xsl:value-of select="$foreign.node.fill.color"/>", 
						fontcolor="<xsl:value-of select="$foreign.node.font.color"/>", 
						shape=record <!-- the sole reason for all the escaping :-( -->
					];
				</xsl:if>
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<xsl:template name="create-depends-edges">
		<xsl:param name="node.name">__UNDEF__</xsl:param>
		<xsl:param name="node.depends">__UNDEF__</xsl:param>
		<xsl:param name="node.depends.index">1</xsl:param>
		<xsl:choose>
			<xsl:when test="contains($node.depends,',')">
				<xsl:call-template name="create-depends-edge">
					<xsl:with-param name="edge.from" select="$node.name"/>
					<xsl:with-param name="edge.to" select="substring-before($node.depends,',')"/>
					<xsl:with-param name="edge.index" select="$node.depends.index"/>
				</xsl:call-template>
				<xsl:call-template name="create-depends-edges">
					<xsl:with-param name="node.name" select="$node.name"/>
					<xsl:with-param name="node.depends" select="substring-after($node.depends,',')"/>
					<xsl:with-param name="node.depends.index" select="$node.depends.index + 1"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:call-template name="create-depends-edge">
					<xsl:with-param name="edge.from" select="$node.name"/>
					<xsl:with-param name="edge.to" select="$node.depends"/>
					<xsl:with-param name="edge.index" select="$node.depends.index"/>
				</xsl:call-template>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template name="create-depends-edge">
		<xsl:param name="edge.from">__UNDEF__</xsl:param>
		<xsl:param name="edge.to">__UNDEF__</xsl:param>
		<xsl:param name="edge.index">__UNDEF__</xsl:param>
		<xsl:if test="$edge.from != '__UNDEF__'">
			<xsl:if test="$edge.to != '__UNDEF__'">
				"<xsl:value-of select="normalize-space($edge.from)"/>"
				<xsl:text> -> </xsl:text>
				"<xsl:value-of select="normalize-space($edge.to)"/>"
				<xsl:if test="$edge.index != '__UNDEF__'"> [label="<xsl:value-of select="$edge.index"/>"];</xsl:if>
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<xsl:template name="create-antcall-edge">
		<xsl:param name="edge.from">__UNDEF__</xsl:param>
		<xsl:param name="edge.to">__UNDEF__</xsl:param>
		<xsl:param name="edge.index">__UNDEF__</xsl:param>
		<xsl:if test="$edge.from != '__UNDEF__'">
			<xsl:if test="$edge.to != '__UNDEF__'">
				"<xsl:value-of select="normalize-space($edge.from)"/>"
				<xsl:text> -> </xsl:text>
				"<xsl:value-of select="normalize-space($edge.to)"/>"
				[style=dotted
				<xsl:if test="$edge.index != '__UNDEF__'">, label="<xsl:value-of select="$edge.index"/>"</xsl:if>];
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<xsl:template name="create-ant-edge">
		<xsl:param name="edge.from">__UNDEF__</xsl:param>
		<xsl:param name="edge.to">__UNDEF__</xsl:param>
		<xsl:param name="edge.index">__UNDEF__</xsl:param>
		<xsl:if test="$edge.from != '__UNDEF__'">
			<xsl:if test="$edge.to != '__UNDEF__'">
				"<xsl:value-of select="normalize-space($edge.from)"/>"
				<xsl:text> -> </xsl:text>
				"<xsl:value-of select="normalize-space($edge.to)"/>"
				[style=dotted
				<xsl:if test="$edge.index != '__UNDEF__'">, label="<xsl:value-of select="$edge.index"/>"</xsl:if>];
			</xsl:if>
		</xsl:if>
	</xsl:template>

	<xsl:template name="create-rank-header">
		{ rank=same;
	</xsl:template>

	<xsl:template name="create-node-rank">
		<xsl:param name="dir.name">__UNDEF__</xsl:param>
		<xsl:param name="antfile.name">__UNDEF__</xsl:param>
		<xsl:param name="node.name">__UNDEF__</xsl:param>
		"<xsl:value-of select="concat(
			normalize-space($dir.name), '_',
			normalize-space($antfile.name), '_', 
			normalize-space($node.name)
		)"/>"; 
	</xsl:template>

	<xsl:template name="create-rank-footer">
		}
	</xsl:template>

	<xsl:template name="create-dot-footer">
		<xsl:param name="dot.graph.label">__UNDEF__</xsl:param>

		<xsl:if test="$dot.graph.label != '__UNDEF__'">
	}<!-- terminate cluster subgraph -->
		</xsl:if>

	/* 
			XSLT SETTINGS AND PARAMETERS<!-- as DOT comments -->

			ant.file -> <xsl:value-of select="$ant.file"/>
			graph.label -> <xsl:value-of select="$graph.label"/>
			graph.rankdir -> <xsl:value-of select="$graph.rankdir"/>

			create.project.node -> <xsl:value-of select="$create.project.node"/>
			use.target.conditions -> <xsl:value-of select="$use.target.conditions"/>
			use.target.description -> <xsl:value-of select="$use.target.description"/>

			ant2dot.xsl.id -> <xsl:value-of select="$ant2dot.xsl.id"/>
			ant2dot.xsl.name -> <xsl:value-of select="$ant2dot.xsl.name"/>

	*/

}<!-- terminate digraph -->
	</xsl:template>

</xsl:stylesheet>
