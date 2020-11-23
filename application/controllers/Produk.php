<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Produk extends CI_Controller
{
  public function __construct()
  {
    parent::__construct();
  }

  public function index()
  {
    $data['judul'] = "BTis | Daftar Produk";
    $data['user'] = $this->User_model->cekData('email', $this->session->userdata('email'));
    $data['kontak'] = $this->db->get('kontak')->row_array();

    // all about data produk
    $data['produk'] = $this->Produk_model->getProdukJoin()->result_array();

    $this->load->view('templates/header', $data);
    $this->load->view('templates/navbar', $data);
    $this->load->view('produk/index');
    $this->load->view('templates/footer', $data);
  }

  public function tambahProduk()
  {
    $data['judul'] = "BTis | Tambah Produk";
    $data['user'] = $this->User_model->cekData('email', $this->session->userdata('email'));
    $data['kontak'] = $this->db->get('kontak')->row_array();

    $data['produk'] = $this->Produk_model->getProduk();
    $data['kategori'] = $this->Produk_model->getKategori();

    $this->Produk_model->rulesProduk();

    if ($this->form_validation->run() == false) {
      $this->load->view('templates/header', $data);
      $this->load->view('templates/navbar', $data);
      $this->load->view('produk/tambah-produk', $data);
      $this->load->view('templates/footer', $data);
    } else {
      $this->Produk_model->tambahProduk($this->input->post(), $_FILES);
      die;
    }
  }

  public function editProduk($id)
  {
    $data['judul'] = "BTis | Edit Produk";
    $data['user'] = $this->User_model->cekData('email', $this->session->userdata('email'));
    $data['kontak'] = $this->db->get('kontak')->row_array();
    $data['produk'] = $this->Produk_model->getProdukId($id);
    $data['kategori'] = $this->Produk_model->getKategori();
    $data['baju'] = ['baju', 'kaema', 'asdad'];

    $this->Produk_model->rulesProduk();

    if ($this->form_validation->run() == false) {
      $this->load->view('templates/header', $data);
      $this->load->view('templates/navbar', $data);
      $this->load->view('produk/edit-produk', $data);
      $this->load->view('templates/footer', $data);
    } else {
      $this->Produk_model->editProduk($this->input->post(), $_FILES, $data['produk']['foto_produk'], $id);
      die;
    }
  }

  public function hapusProduk($id)
  {
    $data['produk'] = $this->Produk_model->getProdukId($id);
    $this->db->delete('produk', ['id_produk' => $id]);
    unlink(FCPATH . 'assets/img/produk/' . $data['produk']['foto_produk']);
    $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-pesan">Produk berhasil hapus!!</div>');
    redirect('produk');
  }

  // kategori
  public function kategori()
  {
    $data['judul'] = "BTis | Daftar Kategori";
    $data['user'] = $this->User_model->cekData('email', $this->session->userdata('email'));
    $data['kontak'] = $this->Produk_model->getOneData('kontak');

    $data['kategori'] = $this->Produk_model->getData('kategori');


    $this->form_validation->set_rules('nama_kategori', 'Nama Kategori', 'required|trim');

    if ($this->form_validation->run() == false) {
      $this->load->view('templates/header', $data);
      $this->load->view('templates/navbar', $data);
      $this->load->view('produk/kategori', $data);
      $this->load->view('templates/footer', $data);
    } else {
      $this->Produk_model->tambahKategori($this->input->post());

      $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-pesan">Kategori baru berhasil ditambahkan!!</div>');
      redirect('produk/kategori');
    }
  }

  public function getKategori()
  {
    $id = $this->input->post('id', true);
    echo json_encode($this->Produk_model->getDataId('kategori', 'id_kategori', $id));
  }

  public function editKategori($id)
  {
    $data['nama_kategori'] = $this->input->post('nama_kategori', true);

    $this->db->where('id_kategori', $id);
    $this->db->update('kategori', $data);
    $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-pesan">Kategori baru berhasil ditambahkan!!</div>');
    redirect('produk/kategori');
  }

  public function hapusKategori($id)
  {

    $this->db->delete('kategori', ['id_kategori' => $id]);
    $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-pesan">Kategori berhasil hapus!!</div>');
    redirect('produk/kategori');
  }

  // produk diskon
  public function produkDiskon()
  {
    $data['judul'] = "BTis | Daftar Produk Diskon";
    $data['user'] = $this->User_model->cekData('email', $this->session->userdata('email'));
    $data['kontak'] = $this->db->get('kontak')->row_array();

    // all about data produk
    $data['produk'] = $this->Produk_model->getProduk();
    $data['diskon'] = $this->Produk_model->getDiskonJoin();

    $this->form_validation->set_rules('id_produk', 'Nama Produk', 'required|is_unique[diskon.id_produk]');
    $this->form_validation->set_rules('diskon', 'Besar Diskon', 'required|trim|numeric');

    if ($this->form_validation->run() == false) {
      $this->load->view('templates/header', $data);
      $this->load->view('templates/navbar', $data);
      $this->load->view('produk/diskon', $data);
      $this->load->view('templates/footer', $data);
    } else {
      $this->Produk_model->tambahDiskon();
      $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-pesan col-12">Diskon berhasil tambahkan!!</div>');
      redirect('produk/produkDiskon');
    }
  }

  public function getDiskon()
  {
    $id = $this->input->post('id', true);
    echo json_encode($this->Produk_model->getDiskonID($id));
  }

  public function editDiskon($id)
  {

    if ($this->input->post('is_active') == null) {
      $is_active = 0;
    } else {
      $is_active = $this->input->post('is_active', true);
    }
    $data = [
      'id_produk' => $this->input->post('id_produk', true),
      'besar_diskon' => $this->input->post('diskon', true),
      'is_active' => $is_active
    ];

    $this->db->where('id_diskon', $id);
    $this->db->update('diskon', $data);
    $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-pesan">Diskon berhasil diubah!!</div>');
    redirect('produk/produkDiskon');
  }

  public function hapusDiskon($id)
  {

    $this->db->delete('diskon', ['id_diskon' => $id]);
    $this->session->set_flashdata('pesan', '<div class="alert alert-success alert-pesan">Diskon berhasil hapus!!</div>');
    redirect('produk/produkDiskon');
  }
}