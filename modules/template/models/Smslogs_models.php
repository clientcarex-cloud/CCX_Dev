    <?php
    
    defined('BASEPATH') or exit('No direct script access allowed');
    
    class Smslogs_models extends App_Model
    {
    public function __construct()
    {
    parent::__construct();
    }
    
    
    public function add_doclist($table,$data){
    
    
    $this->db->where('id',1);
		$this->db->update($table,$data);
    
    }
    public function add_template($table,$data){
    
    
		$this->db->insert($table,$data);
    
    }
    
    public function get_body($id){
    
          $this->db->where('id', $id);
         $project = $this->db->get(db_prefix() . '_smslogs')->row();
         return $project ;
    
    }
    
    public function get_body_edit($id){
    
          $this->db->where('id', $id);
         $project = $this->db->get(db_prefix() . '_smslogs')->row();
         return $project ;
    
    }
    
     public function delete_row($table,$id){
    
        $this->db->where('id',$id);
		$this->db->delete($table);
    
    }
     public function edit($table,$data,$id){
    
        $this->db->where('id',$id);
		$this->db->update($table,$data);
    
    }
    
    /**
    * @param  integer (optional)
    * @return object
    * Get single goal
    */
    
    }