<?php

class VPL_Poll_Pagination {
	
	/*
	 * 
	 */
	protected $current_page;
	
	/*
	 * 
	 */
	protected $max_page;
	
	/*
	 * 
	 */
	protected $items_per_page;
	
	/*
	 * 
	 */
	protected $data = array();
	
	protected $sliced_data = array();
	
	public function __construct( $data , $items_per_page = 10, $prev_text = '&laquo; Previous', $next_text = 'Next &raquo;' ) {
		$this->data = $data;
		$this->items_per_page = $items_per_page;
		$this->prev_text = $prev_text;
		$this->next_text = $next_text;
		$this->max_page = $this->get_max_page();
		$this->current_page = $this->get_current_page();
	}
	
	public function get_max_page() {
		$max_page = (int) ceil( count($this->data) / $this->items_per_page );
		return $max_page;
	}

	public function get_current_page() {
		$current_page = ( (int) $_GET['page'] != 0) ? $_GET['page'] : 1;
		if($current_page > $this->max_page){
			$current_page = $this->max_page;
		}
		return $current_page;
	}
	
	public function get_sliced_data() {
		
		$start = $this->items_per_page * $this->current_page - $this->items_per_page;
		$sliced_data = array_slice($this->data, $start, $this->items_per_page);
		return $sliced_data;
	}
	
	function show_pagination() {
		if( $this->max_page > 1){
			echo '<div class="vpl-pagination">';
			if ( $this->current_page > 1 ) echo '<a href="?page='.($this->current_page - 1).'" >'.$this->prev_text .'</a>';
			 for($i = 1; $i <= $this->max_page ; $i++){
				echo '<a href="?page='.$i.'" '.(($this->current_page != $i)?'':'class="active"').'>'.$i.'</a>';
			}
			if ( $this->current_page < $this->max_page ) echo '<a href="?page='.($this->current_page + 1).'" >'.$this->next_text .'</a>';
			echo '</div>';
		}
	}
}
?>
