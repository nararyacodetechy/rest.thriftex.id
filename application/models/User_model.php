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

    function list($start, $length, $query, $keysearch,$role)
	{
		// echo $query;
		$kolom = ['nama','', 'username', 'email','foto','user_code'];
		$this->db->select('tbl_user.id,tbl_user.nama,tbl_user.username,tbl_user.email,tbl_user.foto,tbl_user.role,tbl_user.validator_brand_id,tbl_user.validator_kategori_id,tbl_user.user_code');
		// tbl_produk_stok.id_barang,tbl_produk_stok.jumlah,tbl_produk_stok.harga_beli
		$this->db->group_start();
		$this->db->or_like($keysearch, $query, 'BOTH');
		$this->db->group_end();

		// if ($this->input->get('iSortCol_0')) {
		// 	for ($i = 0; $i < intval($this->input->get('iSortingCols', TRUE)); $i++) {
		// 		if ($this->input->get('bSortable_' . intval($this->input->get('iSortCol_' . $i)), TRUE) == "true") {
		// 			$this->db->order_by($kolom[intval($this->input->get('iSortCol_' . $i, TRUE))], $this->input->get('sSortDir_' . $i, TRUE));
		// 		}
		// 	}
		// }
		$this->db->where('role',$role);
		$this->db->order_by($this->_table_name.'.'.$this->_primary_key, 'desc');
		return $this->db->get($this->_table_name, $length, $start)->result();
		// echo $this->db->last_query(); die;
	}

    public function count_filter($query,$role)
	{
		$this->db->select('count("id") as qty');
		$this->db->group_start();
		$this->db->or_like('nama', $query, 'BOTH');
		$this->db->group_end();
		$this->db->where($this->_table_name.'.role',$role);
		return $this->db->get($this->_table_name)->row()->qty;
	}

}
