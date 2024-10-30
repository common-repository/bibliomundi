<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Category_BiblioMundi' ) ) :

class WC_Category_BiblioMundi {

	protected static function exists( $code, $type ) {
		if ( $code ) {
			$categories = false;

			switch( $type ) {
				case WC_Category_Type_BiblioMundi::BISAC :
					$categories = self::_get_bisac();
					break;
				case WC_Category_Type_BiblioMundi::CDD :
					$categories = self::_get_cdd();
					break;
			}

			if ( $categories && array_key_exists( $code, $categories ) ) {
				return $categories[$code];
			}				
		}

		return false;
	}

	protected static function insert( $code, $type, $taxonomy, $ebook_term_id = 0) {
		$category = self::exists( $code, $type );
        $arg = array();
		if ( $category ) {
		    if ($ebook_term_id) {
		        $arg['parent'] = $ebook_term_id;
            }

			$term = wp_insert_term( $category, $taxonomy, $arg );
			if ( is_wp_error( $term ) && isset( $term->error_data ) ) {
				return $term->error_data['term_exists'];
			} else {
				return $term['term_id'];
			}
		}

		return false;
	}

	public static function add_relationship( $post_id, $code, $type, $taxonomy = 'product_cat', $ebook_term_id = 0) {

		$term_id = self::insert( $code, $type, $taxonomy, $ebook_term_id);

		if ( $term_id ) {
			return wp_set_post_terms( $post_id, array( $term_id ), $taxonomy );
		}
		
		return false;
	}
	
	private static function _get_bisac( $code = null ) {
		return apply_filters( 
			'woocommerce_bisac_categories_bibliomundi', 
			array(
				'000'    => 'CIÊNCIA DA COMPUTAÇÃO',
				'010'    => 'BIBLIOGRAFIA',
				'020'    => 'BIBLIOTECONOMIA E CIÊNCIA DA INFORMAÇÃO',
				'028.5'  => 'LITERATURA INFANTO-JUVENIL',
				'030'    => 'ENCICLOPÉDIAS GERAIS',
				'050'    => 'PERIÓDICOS',
				'060'    => 'SOCIEDADES',
				'070'    => 'JORNALISMO',
				'080'    => 'COLEÇÕES DE OBRAS DIVERSAS SEM ASSUNTO ESPECÍFICO',
				'090'    => 'MANUSCRITOS',
				'100'    => 'FILOSOFIA E PSICOLOGIA',
				'110'    => 'METAFÍSICA',
				'120'    => 'TEORIA DO CONHECIMENTO',
				'130'    => 'PARAPSICOLOGIA',
				'140'    => 'ESCOLAS FILOSÓFICAS ESPECÍFICAS',
				'150'    => 'PSICOLOGIA',
				'160'    => 'LÓGICA',
				'170'    => 'ÉTICA',
				'180'    => 'FILOSOFIA ANTIGA',
				'190'    => 'FILOSOFIA MODERNA OCIDENTAL',
				'200'    => 'RELIGIÃO',
				'210'    => 'FILOSOFIA E TEORIA DA RELIGIÃO',
				'220'    => 'BÍBLIA',
				'230'    => 'CRISTIANISMO',
				'240'    => 'MORAL CRISTÃ E TEOLOGIA DEVOCIONAL',
				'250'    => 'CONGREGAÇÕES CRISTÃS',
				'260'    => 'TEOLOGIA SOCIAL E ECLESIÁSTICA CRISTÃ',
				'270'    => 'HISTÓRIA DO CRISTIANISMO',
				'280'    => 'DENOMINAÇÕES E SEITAS CRISTÃS',
				'290'    => 'OUTRAS RELIGIÕES',
				'300'    => 'CIÊNCIAS SOCIAIS',
				'310'    => 'COLEÇÕES DE ESTATÍSTICAS GERAIS',
				'320'    => 'CIÊNCIA POLÍTICA',
				'330'    => 'ECONOMIA',
				'340'    => 'DIREITO',
				'350'    => 'ADMINISTRAÇÃO PÚBLICA E CIÊNCIA MILITAR',
				'360'    => 'SERVIÇOS E PROBLEMAS SOCIAIS ASSOCIAÇÕES',
				'370'    => 'EDUCAÇÃO',
				'380'    => 'COMÉRCIO',
				'390'    => 'USOS E COSTUMES',
				'400'    => 'LINGUAGEM E LÍNGUAS',
				'403'    => 'DICIONÁRIOS E ENCICLOPÉDIAS',
				'410'    => 'LINGÜÍSTICA',
				'420'    => 'LÍNGUA INGLESA',
				'430'    => 'LÍNGUA ALEMÃ',
				'440'    => 'LÍNGUA FRANCESA',
				'450'    => 'LÍNGUA ITALIANA',
				'460'    => 'LÍNGUA ESPANHOLA',
				'469'    => 'LÍNGUA PORTUGUESA',
				'470'    => 'LÍNGUA LATINA',
				'480'    => 'LÍNGUA GREGA CLÁSSICA E MODERNA',
				'490'    => 'OUTRAS LÍNGUAS',
				'500'    => 'CIÊNCIAS NATURAIS',
				'510'    => 'MATEMÁTICA',
				'520'    => 'ASTRONOMIA E CIÊNCIAS AFINS',
				'530'    => 'FÍSICA',
				'540'    => 'QUÍMICA E CIÊNCIAS AFINS',
				'550'    => 'GEOCIÊNCIAS CIÊNCIAS DA TERRA',
				'560'    => 'PALEONTOLOGIA',
				'570'    => 'BIOLOGIA',
				'580'    => 'PLANTAS (BOTÂNICA)',
				'590'    => 'ANIMAIS (ZOOLOGIA)',
				'600'    => 'TECNOLOGIA (CIÊNCIAS APLICADAS)',
				'602'    => 'TECNOLOGIA - MISCELÂNIA',
				'610'    => 'MEDICINA E SAÚDE',
				'620'    => 'ENGENHARIA',
				'630'    => 'AGRICULTURA E TECNOLOGIAS RELACIONADAS',
				'640'    => 'ECONOMIA DOMÉSTICA ADMINISTRAÇÃO DA FAMÍLIA E DO LAR',
				'641.5'  => 'CULINÁRIA',
				'650'    => 'ADMINISTRAÇÃO E SERVIÇOS AUXILIARES',
				'657'    => 'CONTABILIDADE',
				'660'    => 'ENGENHARIA QUÍMICA E TECNOLOGIAS RELACIONADAS',
				'670'    => 'PRODUTOS MANUFATURADOS',
				'680'    => 'MANUFATURA PARA USOS ESPECÍFICOS',
				'690'    => 'CONSTRUÇÕES',
				'700'    => 'ARTES',
				'710'    => 'PLANEJAMENTO URBANO E PAISAGISMO',
				'720'    => 'ARQUITETURA',
				'730'    => 'ARTES PLÁSTICAS ESCULTURA',
				'740'    => 'DESENHO E ARTES DECORATIVAS',
				'750'    => 'PINTURA',
				'760'    => 'ARTES GRÁFICAS GRAVURAS',
				'770'    => 'FOTOGRAFIA E ARTE POR COMPUTADOR',
				'780'    => 'MÚSICA',
				'790'    => 'ARTES CÊNICAS E RECREATIVAS; ESPORTES',
				'800'    => 'LITERATURA E RETÓRICA',
				'810'    => 'LITERATURA AMERICANA',
				'820'    => 'LITERATURA INGLESA',
				'830'    => 'LITERATURA ALEMÃ',
				'840'    => 'LITERATURA FRANCESA',
				'850'    => 'LITERATURA ITALIANA',
				'860'    => 'LITERATURA ESPANHOLA',
				'869'    => 'LITERATURA PORTUGUESA',
				'870'    => 'LITERATURA LATINA',
				'880'    => 'LITERATURA GREGA',
				'890'    => 'OUTRAS LITERATURAS',
				'900'    => 'GEOGRAFIA E HISTÓRIA',
				'910'    => 'GEOGRAFIA E VIAGENS',
				'918.1'  => 'GEOGRAFIA E VIAGENS - BRASIL',
				'920'    => 'BIOGRAFIAS',
				'930'    => 'HISTÓRIA DO MUNDO ANTIGO ATÉ CA. 499',
				'940'    => 'HISTÓRIA DA EUROPA',
				'950'    => 'HISTÓRIA DA ÁSIA ORIENTE',
				'960'    => 'HISTÓRIA DA ÁFRICA',
				'970'    => 'HISTÓRIA DA AMÉRICA DO NORTE',
				'980'    => 'HISTÓRIA DA AMÉRICA DO SUL',
				'981'    => 'HISTÓRIA DO BRASIL',
				'990'    => 'HISTÓRIA DE OUTRAS REGIÕES',
				'B869'   => 'LITERATURA BRASILEIRA',
				'B869.1' => 'POESIA BRASILEIRA',
				'B869.2' => 'TEATRO BRASILEIRO',
				'B869.3' => 'FICÇÃO E CONTOS BRASILEIROS',
				'B869.4' => 'ENSAIOS BRASILEIROS',
				'B869.5' => 'DISCURSOS BRASILEIROS',
				'B869.6' => 'CARTAS BRASILEIRAS',
				'B869.7' => 'HUMOR E SÁTIRAS BRASILEIRAS',
				'B869.8' => 'MISCELÂNEA DE ESCRITOS BRASILEIROS',
			)
		);
	}

	private static function _get_cdd() {
		return apply_filters(
			'woocommerce_cdd_categories_bibliomundi',
			array(
				"000"    => "CIÊNCIA DA COMPUTAÇÃO, INFORMAÇÃO, OBRAS GERAIS",
				"010"    => "BIBLIOGRAFIA",
				"020"    => "BIBLIOTECONOMIA E CIÊNCIA DA INFORMAÇÃO",
				"028.5"  => "LITERATURA INFANTO-JUVENIL",
				"030"    => "ENCICLOPÉDIAS GERAIS",
				"050"    => "PERIÓDICOS",
				"060"    => "SOCIEDADES, ORGANIZAÇÕES E MUSEOLOGIA",
				"070"    => "JORNALISMO, EDITORAÇÃO, IMPRENSA DOCUMENTÁRIA E EDUCATIVA",
				"080"    => "COLEÇÕES DE OBRAS DIVERSAS SEM ASSUNTO ESPECÍFICO",
				"090"    => "MANUSCRITOS, OBRAS RARAS E OUTROS MATERIAIS RAROS IMPRESSOS",
				"100"    => "FILOSOFIA E PSICOLOGIA",
				"110"    => "METAFÍSICA",
				"120"    => "TEORIA DO CONHECIMENTO, CAUSALIDADE E SER HUMANO",
				"130"    => "PARAPSICOLOGIA, OCULTISMO E ESPIRITISMO",
				"140"    => "ESCOLAS FILOSÓFICAS ESPECÍFICAS",
				"150"    => "PSICOLOGIA",
				"160"    => "LÓGICA",
				"170"    => "ÉTICA",
				"180"    => "FILOSOFIA ANTIGA, MEDIEVAL E ORIENTAL",
				"190"    => "FILOSOFIA MODERNA OCIDENTAL",
				"200"    => "RELIGIÃO",
				"210"    => "FILOSOFIA E TEORIA DA RELIGIÃO",
				"220"    => "BÍBLIA",
				"230"    => "CRISTIANISMO",
				"240"    => "MORAL CRISTÃ E TEOLOGIA DEVOCIONAL",
				"250"    => "CONGREGAÇÕES CRISTÃS, PRÁTICA E TEOLOGIA PASTORAL",
				"260"    => "TEOLOGIA SOCIAL E ECLESIÁSTICA CRISTÃ",
				"270"    => "HISTÓRIA DO CRISTIANISMO",
				"280"    => "DENOMINAÇÕES E SEITAS CRISTÃS",
				"290"    => "OUTRAS RELIGIÕES",
				"300"    => "CIÊNCIAS SOCIAIS",
				"310"    => "COLEÇÕES DE ESTATÍSTICAS GERAIS",
				"320"    => "CIÊNCIA POLÍTICA",
				"330"    => "ECONOMIA",
				"340"    => "DIREITO",
				"350"    => "ADMINISTRAÇÃO PÚBLICA E CIÊNCIA MILITAR",
				"360"    => "SERVIÇOS E PROBLEMAS SOCIAIS ASSOCIAÇÕES",
				"370"    => "EDUCAÇÃO",
				"380"    => "COMÉRCIO, COMUNICAÇÕES E TRANSPORTE",
				"390"    => "USOS E COSTUMES, ETIQUETA E FOLCLORE",
				"400"    => "LINGUAGEM E LÍNGUAS",
				"403"    => "DICIONÁRIOS E ENCICLOPÉDIAS",
				"410"    => "LINGÜÍSTICA",
				"420"    => "LÍNGUA INGLESA",
				"430"    => "LÍNGUA ALEMÃ",
				"440"    => "LÍNGUA FRANCESA",
				"450"    => "LÍNGUA ITALIANA",
				"460"    => "LÍNGUA ESPANHOLA",
				"469"    => "LÍNGUA PORTUGUESA",
				"470"    => "LÍNGUA LATINA",
				"480"    => "LÍNGUA GREGA CLÁSSICA E MODERNA",
				"490"    => "OUTRAS LÍNGUAS",
				"500"    => "CIÊNCIAS NATURAIS",
				"510"    => "MATEMÁTICA",
				"520"    => "ASTRONOMIA E CIÊNCIAS AFINS",
				"530"    => "FÍSICA",
				"540"    => "QUÍMICA E CIÊNCIAS AFINS",
				"550"    => "GEOCIÊNCIAS CIÊNCIAS DA TERRA",
				"560"    => "PALEONTOLOGIA, PALEOZOOLOGIA",
				"570"    => "BIOLOGIA, CIÊNCIAS DA VIDA",
				"580"    => "PLANTAS (BOTÂNICA)",
				"590"    => "ANIMAIS (ZOOLOGIA)",
				"600"    => "TECNOLOGIA (CIÊNCIAS APLICADAS)",
				"602"    => "TECNOLOGIA - MISCELÂNIA",
				"610"    => "MEDICINA E SAÚDE",
				"620"    => "ENGENHARIA",
				"630"    => "AGRICULTURA E TECNOLOGIAS RELACIONADAS",
				"640"    => "ECONOMIA DOMÉSTICA ADMINISTRAÇÃO DA FAMÍLIA E DO LAR",
				"641.5"  => "CULINÁRIA",
				"650"    => "ADMINISTRAÇÃO E SERVIÇOS AUXILIARES",
				"657"    => "CONTABILIDADE",
				"660"    => "ENGENHARIA QUÍMICA E TECNOLOGIAS RELACIONADAS",
				"670"    => "PRODUTOS MANUFATURADOS",
				"680"    => "MANUFATURA PARA USOS ESPECÍFICOS",
				"690"    => "CONSTRUÇÕES",
				"700"    => "ARTES",
				"710"    => "PLANEJAMENTO URBANO E PAISAGISMO",
				"720"    => "ARQUITETURA",
				"730"    => "ARTES PLÁSTICAS ESCULTURA",
				"740"    => "DESENHO E ARTES DECORATIVAS",
				"750"    => "PINTURA",
				"760"    => "ARTES GRÁFICAS GRAVURAS",
				"770"    => "FOTOGRAFIA E ARTE POR COMPUTADOR",
				"780"    => "MÚSICA",
				"790"    => "ARTES CÊNICAS E RECREATIVAS; ESPORTES",
				"800"    => "LITERATURA E RETÓRICA",
				"810"    => "LITERATURA AMERICANA",
				"820"    => "LITERATURA INGLESA",
				"830"    => "LITERATURA ALEMÃ",
				"840"    => "LITERATURA FRANCESA",
				"850"    => "LITERATURA ITALIANA",
				"860"    => "LITERATURA ESPANHOLA",
				"869"    => "LITERATURA PORTUGUESA",
				"870"    => "LITERATURA LATINA",
				"880"    => "LITERATURA GREGA",
				"890"    => "OUTRAS LITERATURAS, LITERATURAS EM OUTROS IDIOMAS",
				"900"    => "GEOGRAFIA E HISTÓRIA",
				"910"    => "GEOGRAFIA E VIAGENS",
				"918.1"  => "GEOGRAFIA E VIAGENS - BRASIL",
				"920"    => "BIOGRAFIAS, GENEALOGIA, INSÍGNIA",
				"930"    => "HISTÓRIA DO MUNDO ANTIGO ATÉ CA. 499",
				"940"    => "HISTÓRIA DA EUROPA",
				"950"    => "HISTÓRIA DA ÁSIA ORIENTE",
				"960"    => "HISTÓRIA DA ÁFRICA",
				"970"    => "HISTÓRIA DA AMÉRICA DO NORTE",
				"980"    => "HISTÓRIA DA AMÉRICA DO SUL",
				"981"    => "HISTÓRIA DO BRASIL",
				"990"    => "HISTÓRIA DE OUTRAS REGIÕES",
				"B869"   => "LITERATURA BRASILEIRA",
				"B869.1" => "POESIA BRASILEIRA",
				"B869.2" => "TEATRO BRASILEIRO",
				"B869.3" => "FICÇÃO E CONTOS BRASILEIROS",
				"B869.4" => "ENSAIOS BRASILEIROS",
				"B869.5" => "DISCURSOS BRASILEIROS",
				"B869.6" => "CARTAS BRASILEIRAS",
				"B869.7" => "HUMOR E SÁTIRAS BRASILEIRAS",
				"B869.8" => "MISCELÂNEA DE ESCRITOS BRASILEIROS",
			)
		);
	}

}

endif;