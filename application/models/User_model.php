<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends MY_Model
{

    protected $_table_name = 'tbl_user';
	protected $_primary_key = 'id';
	protected $_order_by = 'id';
	protected $_order_by_type = 'desc';
    // public function getMahasiswa($id = null){
    //     if($id == null){
    //         return $this->db->get('mahasiswa')->result_array();
    //     }else{
    //         return $this->db->get_where('mahasiswa',['id' => $id])->result_array();
    //     }
    // }

    // public function deleteMahasiswa($id){
    //     $this->db->delete('mahasiswa',['id'=>$id]);
    //     return $this->db->affected_rows();
    // }

    public function createUser($data){
        $this->db->insert('tbl_user',$data);
        return $this->db->affected_rows();
    }

    // public function updateMahasiswa($data,$id){
    //     $this->db->update('mahasiswa',$data,['id' => $id]);
    //     return $this->db->affected_rows();

    // }
}
