
if($result[1] > 0){
			//$output .= '<br />';
			//$link_see_all = prepare_link('categories', '', '', 'all', _SEE_ALL);
	//> Mostra a Label Categoria
			//$output = $output.draw_sub_title_bar(_CATEGORIES, false);
    //> Inicia a criacao de Tabela
			$output = '<table border="0" width="100%" align="center" cellspacing="5" class="categories_table">';
			$output = $output.'<tr>';
    //> Precorre o ARRAY result: indice 0 => contem CATEGORIAS, indice 1 => contem o tamanho do ARRAY
			for($i=0; $i < $result[1]; $i++){
    //> Se o ARRAY estiver vasio imprime uma linha vazia e abre outra linha. 
				if($i != 0 && $i % $categories_columns == 0) $output = $output.'</tr><tr>';
	//> Verifiva se o modulo image esta activo. 		
				if($categories_images){
					$output = $output.'<td valign="top" width="40px">';
	//> Carrega a imagem ('icon_thumb'), e Imprime na <td></td>
					$icon_file_thumb = ($result[0][$i]['icon_thumb'] != '') ? $result[0][$i]['icon_thumb'] : 'no_image.png';
					$output = $output.'<img src="images/categories/'.$icon_file_thumb.'" width="64px" height="64px" alt="'.$result[0][$i]['name'].'" title="'.$result[0][$i]['name'].'" />';
					$output = $output.'</td>';
				}
				
				$output = $output.'<td valign="top" width="'.intval(100/$categories_columns).'%">';
	//> Imprime link da Categoria			
				$output = $output.prepare_link('category', 'cid', $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], 'category_link', $result[0][$i]['description']).' <span class="categories_span">('.$result[0][$i][$listings_count_field].')</span>';
	//> Imprime link em Subcategorias
				$result_1 = database_query(str_replace('_PARENT_ID_', $result[0][$i]['id'], $sql), DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
				$output .= '<br><div style="padding-top:5px;">';				
				for($j=0; ($j < $result_1[1] && $j <= $sub_categories_count); $j++){
					if($j > 0) $output = $output.', ';
					if($j < $sub_categories_count){
	//> Separa as subcategorias com virgula. 
						$output = $output.prepare_link('category', 'cid', $result_1[0][$j]['id'], $result_1[0][$j]['name'], $result_1[0][$j]['name'], 'sub_category_link', $result_1[0][$j]['description']).' <span class="sub_categories_span">('.$result_1[0][$j][$listings_count_field].')</span>';					
					}else{
						$output = $output.prepare_link('category', 'cid', $result[0][$i]['id'], _MORE, _MORE.'...', 'sub_category_link', _READ_MORE);
					}					
				}
				$output = $output.'<div>';								
				$output = $output.'</td>';
			}
			$output = $output.'</tr>';
			$output = $output.'</table>';
		}
		=========================================================================

























if($result[1] > 0){
			//$output .= '<br />';
			//$link_see_all = prepare_link('categories', '', '', 'all', _SEE_ALL);
			$output .= draw_sub_title_bar(_CATEGORIES, false);
			$output .= '<table border="0" width="100%" align="center" cellspacing="5" class="categories_table">';
			$output .= '<tr>';
			for($i=0; $i < $result[1]; $i++){
				if($i != 0 && $i % $categories_columns == 0) $output .= '</tr><tr>';
				
				if($categories_images){
					$output .= '<td valign="top" width="40px">';
					$icon_file_thumb = ($result[0][$i]['icon_thumb'] != '') ? $result[0][$i]['icon_thumb'] : 'no_image.png';
					$output .= '<img src="images/categories/'.$icon_file_thumb.'" width="64px" height="64px" alt="'.$result[0][$i]['name'].'" title="'.$result[0][$i]['name'].'" />';
					$output .= '</td>';
				}
				
				$output .= '<td valign="top" width="'.intval(100/$categories_columns).'%">';
				$output .= prepare_link('category', 'cid', $result[0][$i]['id'], $result[0][$i]['name'], $result[0][$i]['name'], 'category_link', $result[0][$i]['description']).' <span class="categories_span">('.$result[0][$i][$listings_count_field].')</span>';
				$result_1 = database_query(str_replace('_PARENT_ID_', $result[0][$i]['id'], $sql), DATA_AND_ROWS, ALL_ROWS, FETCH_ASSOC);
				$output .= '<br><div style="padding-top:5px;">';				
				for($j=0; ($j < $result_1[1] && $j <= $sub_categories_count); $j++){
					if($j > 0) $output .= ', ';
					if($j < $sub_categories_count){
						$output .= prepare_link('category', 'cid', $result_1[0][$j]['id'], $result_1[0][$j]['name'], $result_1[0][$j]['name'], 'sub_category_link', $result_1[0][$j]['description']).' <span class="sub_categories_span">('.$result_1[0][$j][$listings_count_field].')</span>';					
					}else{
						$output .= prepare_link('category', 'cid', $result[0][$i]['id'], _MORE, _MORE.'...', 'sub_category_link', _READ_MORE);
					}					
				}
				$output .= '<div>';								
				$output .= '</td>';
			}
			$output .= '</tr>';
			$output .= '</table>';
		}