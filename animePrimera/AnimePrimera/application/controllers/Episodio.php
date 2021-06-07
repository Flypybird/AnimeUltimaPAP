<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Episodio extends CI_Controller {

    public function __construct(){
        parent::__construct();
        $this->load->library(array('session','parser','image_lib'));
        $this->load->helper(array('text','string','url','form','file'));
        $this->load->model('login_model');
        $this->load->model('main_model');
        if ($this->login_model->isLoggedIn()) {
            $this->data['user'] = $this->session->userdata('user');
            $this->data['estado'] = 1;
            $this->data['seg'] = FALSE;
            $user = $this->data['user'];
            $this->data['fotoPerfil'] = $user['FotoPerfil'];
        }
    }

	public function index()
	{
        if($this->login_model->isLoggedIn() == true){
            $user = $this->data['user'];
            /*$perms = $this->getPerms($user['perms']);
            $this->data['perms'] = $perms;*/
            $this->data['fotoPerfil'] = $user['FotoPerfil'];
        }
        $this->data['titulo'] = 'AnimePrimera ADM';
        $this->data['series'] = $this->main_model->get_table('series');

        $this->load->view('animeprimeraadm',$this->data);
	}

	public function gerirEps(){
        if(isset($_POST['Editar'])){
            if(isset($_POST['idEpisodio'])){
                $values = array(
                    'url' => $_POST['url'],
                    'titulo' => $_POST['titulo'],
                    'dataRelease' => $_POST['dataRelease']
                );
                $this->main_model->edit('idEpisodio','episodio',$_POST['idEpisodio'],$values);
                redirect();
            }else{
                $this->data['query'] = $this->main_model->get_main_where('episodio','idEpisodio',$_POST['Editar']);
                $this->data['idEpisodio'] = $_POST['Editar'];
                $this->load->view('editarEpisodio',$this->data);
            }

        }elseif(isset($_POST['Remover'])){
            $this->main_model->delete('idEpisodio','episodio',$_POST['Remover']);
            redirect();
        }else{
            $idTemporada = $this->uri->segment(3);
            $this->data['idTemporada'] = $idTemporada;
            $query = $this->main_model->get_main_where('temporadas','idTemporada',$idTemporada);
            $querye = $this->main_model->get_main_where_array('episodio','idTemporada', $idTemporada);
            $this->data['query'] = $query;
            $this->data['querye'] = $querye;
            print_r($query);
            $this->data['querys'] = $this->main_model->get_main_where('series','idSerie',$query[0]->idSerie);
            $this->load->view('gerirEpisodio',$this->data);
        }

    }

    public function addEps()
    {
        if(isset($_POST['Criar'])){
            echo "entrou";
            //$animeName = str_replace(' ', '%20' ,$_POST['animeName']);
            //$url = 'http://localhost:3000/getAnimeEpisode/' . $animeName . '/' . $_POST['animeEps'];
            //$infoeps = file_get_contents($url);
            //print_r($infoeps);
            $video = $this->UploadVideo($_POST);
            $url = $video['video_path'] . $video['video_name'];
            $videoname = $_POST['animeName'] . ' | ' . $_POST['animeEps'];
            $values = array(
                'url' => $url,
                'idTemporada' => $_POST['idTemporada'],
                'titulo' => $videoname
            );
            print_r($values);
            $this->main_model->add('episodio',$values);
            redirect();
        }else{
            $idTemporada = $this->uri->segment(3);
            echo $idTemporada;
            $this->data['idTemporada'] = $idTemporada;
            $this->load->view('addEpisodio',$this->data);
        }
    }

    public function watchepisode(){
        if($this->login_model->isLoggedIn() == true){
            $user = $this->data['user'];
            /*$perms = $this->getPerms($user['perms']);
            $this->data['perms'] = $perms;*/
            $this->data['fotoPerfil'] = $user['FotoPerfil'];
            $this->data['idUser'] = $user['idUser'];
        }
        $idEpisodio = $this->uri->segment(3);
        $query = $this->main_model->get_main_where('episodio','idEpisodio',$idEpisodio);
        $queryTemp = $this->main_model->get_main_where('temporadas','idTemporada',$query[0]->idTemporada);
        $querySerie = $this->main_model->get_main_where('series','idSerie',$queryTemp[0]->idSerie);
        $queryComentarios = $this->main_model->get_main_where('comentario','idEpisodio',$idEpisodio);
        $this->data['query'] = $query;
        //print_r($queryComentarios);
        if(!empty($queryComentarios)){
            $queryUserCom = $this->main_model->get_both_main_where($idEpisodio);
            //$queryUserCom = $this->db->query('SELECT * FROM user u INNER JOIN comentario c ON u.idUser = c.idUser WHERE u.idUser =' . $queryComentarios[0]->idUser);
            $this->data['comentarios'] = $queryUserCom;
        }else{
            $this->data['comentarios'] = array();
        }
        //print_r($queryUserCom);
        $this->data['idEpisodio'] = $idEpisodio;
        $recommended = $this->getRecommended($querySerie[0]->Tipo);
        $q = array();
        foreach ($recommended as $r){
            $q[] = $this->main_model->get_main_where('series','idSerie',$r->idSerie);
        }
        $this->data['recommended'] = array_slice($q,0,5);
        $this->load->view('watcheps',$this->data);
    }

    public function addComment(){
        if(isset($_POST['Submeter'])){
            if($this->login_model->isLoggedIn() == true) {
                $values = array(
                    'idUser' => $_POST['idUser'],
                    'idEpisodio' => $_POST['idEpisodio'],
                    'texto' => $_POST['coment']
                );
                $this->main_model->add('comentario',$values);
                redirect(base_url('Episodio/watchepisode/'.$_POST['idEpisodio']));
            }else{

            }
        }
    }

    private function getRecommended($category){
        $categoryArray = explode(',',$category);
        $querymerged = array();
        for($i = 0; $i < count($categoryArray); $i++){
            $tipo = $categoryArray[$i];
            $query = $this->main_model->get_main_where('series','Tipo', $tipo);
            $querymerged = array_merge($querymerged, $query);
        }

        return $querymerged;
    }

    //appends all error messages
    private function handle_error($err) {
        $this->error .= $err . "\r\n";
    }

    //appends all success messages
    private function handle_success($succ) {
        $this->success .= $succ . "\r\n";
    }


    private function UploadVideo($inputFileName){
        if ($this->input->post('video_upload')) {
            //set preferences
            //file upload destination
            $upload_path =  "resources/vid/";
            $config['upload_path'] = $upload_path;
            //allowed file types. * means all types
            $config['allowed_types'] = '*';
            //allowed max file size. 0 means unlimited file size
            $config['max_size'] = '0';
            //max file name size
            $config['max_filename'] = '255';
            //whether file name should be encrypted or not
            $config['encrypt_name'] = FALSE;
            //store video info once uploaded
            $video_data = array();
            //check for errors
            $is_file_error = FALSE;
            //check if file was selected for upload
            if (!$_FILES) {
                $is_file_error = TRUE;
                $this->handle_error('Select a video file.');
            }
            //if file was selected then proceed to upload
            if (!$is_file_error) {
                //load the preferences
                $this->load->library('upload', $config);
                //check file successfully uploaded. 'video_name' is the name of the input
                if (!$this->upload->do_upload('video_name')) {
                    //if file upload failed then catch the errors
                    $this->handle_error($this->upload->display_errors());
                    $is_file_error = TRUE;
                } else {
                    //store the video file info
                    $video_data = $this->upload->data();
                }
            }
            // There were errors, you have to delete the uploaded video
            if ($is_file_error) {
                if ($video_data) {
                    $file = $upload_path . $video_data['file_name'];
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
            } else {
                $data['video_name'] = $video_data['file_name'];
                $data['video_path'] = $upload_path;
                $data['video_type'] = $video_data['file_type'];
                $this->handle_success('Video was successfully uploaded to direcoty <strong>' . $upload_path . '</strong>.');
            }
        }
        //load the error and success messages
        $data['errors'] = $this->error;
        $data['success'] = $this->success;
        return $data;
    }

    private function UploadFile($inputFileName)
    {
        /*
         * O CodeIgniter possui uma biblioteca nativa para trabalhar com upload de arquivos, chamada File Uploading.
         */
        $this->load->library('upload');
        //Definimos um caminho para upload, neste caso será na raiz /app2
        $path = "./resources/img/seriesthumb/";

        //Definimos as configurações para o upload

        //Determinamos o path para gravar o arquivo
        $config['upload_path'] = $path;

        //Definimos os tipos de arquivos suportados
        $config['allowed_types'] = 'jpg|png|gif|pdf|zip|rar|doc|xls';

        //Definimos o maximo permitido
        //Php.ini definimos os tamanhos permitidos
        //post_max_size=15M -> pelo POST
        // upload_max_size=15M // Por Upload
        $config['max_size'] = '51120';//em KB

        //Definimos que o nome do arquivo será criptografado
        $config['encrypt_name'] = TRUE;

        //Verificamos se o diretório existe se não existe criamos com permissão de leitura e escrita
        if (!is_dir($path))
            mkdir($path, 0777, $recursive = true);

        //Setamos as configurações para a library upload
        $this->upload->initialize($config);

        if (!$this->upload->do_upload($inputFileName)) {
            //Em caso de erro retornamos os mesmos para uma variável e enviamos para a view
            $data['error'] = true;
            $data['message'] = $this->upload->display_errors();
        } else {
            $data['error'] = false;

            //Recuperamos os dados do arquivo e enviamos para o array da view
            $data['fileData'] = $this->upload->data();
            $arquivoPath = $path . "/" . $data['fileData']['file_name'];
            //Passando para o array $data
            $data['urlArquivo'] = base_url($arquivoPath);

            //Definimos a URL para download
            $downloadPath = base_url('resources/img/') . $data['fileData']['file_name'];
            //Passando para o Array Data
            $data['urlDownload'] = base_url($downloadPath);


        }
        return $data;
    }


}
