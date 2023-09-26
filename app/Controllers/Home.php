<?php

namespace App\Controllers;
use CodeIgniter\API\ResponseTrait;
use App\Models\Testimonial;

class Home extends BaseController
{
    use ResponseTrait;
    public function index($status)
    {
        $testi = new Testimonial();
        $data = $testi->where('status', $status)->select('*')
                        ->first();
        if(is_null($data) || empty($data)){
            return $this->respond([
                'statusCode' => 400,
                'message' => 'ANY TESTIMONIAL FOUND'
            ]);
        }
        return $this->respond($data, 200, 'list of testimonial');
    }
    public function saveTestimonial()
    {
       $rules = $this->validate([
           'internaut_name' => [
               'rules' => 'trim|required',
               'errors' => [
                   'required' => 'Le champ nom de l\'internaute est requis',
               ],
           ],
           'content' => [
               'rules' => 'trim|required',
               'errors' => 'Veuillez ecrire quelque chose',
           ],
       ]);
       if(!$rules){
           return $this->respond([
               'statusCode' => 400,
               'message' => $this->validator->getErrors(),
           ]);
       }
       $data = [
           'content' => $this->request->getPost('content'),
           'internaut_email' => $this->request->getPost('email'),
           'internaut_name' => $this->request->getPost('name'),
       ];
       $testim = new Testimonial();
       $testi = $testim->insert($data);
       if(is_null($testi) || empty($testi)){
           return $this->respond([
               'statusCode' => 400,
               'message' => 'AN ERROR HAS OCCURRED',
           ]);
       }
       $data = $testim->find($testi);
       if(is_null($data) || empty($data)){
           return $this->respond([
               'statusCode' => 400,
               'message' => 'YOUR TESTIMONIAL NOT FOUND',
           ]);
       }
       return $this->respond($data, 200, 'Votre Temoignage');
    }
}
