<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Models\Bed;
use OpenApi\Annotations as OA;


/**
 * Class BedController,
 * 
 * @author Victorio <victorio.422023022@civitas.ukrida.ac.id>
 */

class BedController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/bed",
     *     tags={"Bed"},
     *     summary="Display a listing of items",
     *     operationId="index",
     *     @OA\Response(
     *         response=200,
     *         description="successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="_page",
     *         in="query",
     *         description="current page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             example=1
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_limit",
     *         in="query",
     *         description="max item in a page",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64",
     *             example=10
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_search",
     *         in="query",
     *         description="word to search",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_type",
     *         in="query",
     *         description="search by type like name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="_sort_by",
     *         in="query",
     *         description="word to search",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="latest"
     *         )
     *     ),
     * )
     */

    public function index(Request $request)
    {
        try {
            $data['filter']       = $request->all();
            $page                 = $data['filter']['_page']  = (@$data['filter']['_page'] ? intval($data['filter']['_page']) : 1);
            $limit                = $data['filter']['_limit'] = (@$data['filter']['_limit'] ? intval($data['filter']['_limit']) : 1000);
            $offset               = ($page?($page-1)*$limit:0);
            $data['products']     = Bed::whereRaw('1 = 1');
            
            
            if($request->get('_search')){
                $data['products'] = $data['products']->whereRaw('(LOWER(product_name) LIKE "%'.strtolower($request->get('_search')).'%")');
            }
            if($request->get('_type')){
                $data['products'] = $data['products']->whereRaw('LOWER(type) = "'.strtolower($request->get('_type')).'"');
            }
            if($request->get('_sort_by')){
            switch ($request->get('_sort_by')) {
                default:
                case 'first_added':
                $data['products'] = $data['products']->orderBy('id','ASC');
                break;
                case 'latest_added':
                $data['products'] = $data['products']->orderBy('created_at','DESC');
                break;
                case 'product_name_asc':
                $data['products'] = $data['products']->orderBy('product_name','ASC');
                break;
                case 'product_name_desc':
                $data['products'] = $data['products']->orderBy('product_name','DESC');
                break;
                case 'price_asc':
                $data['products'] = $data['products']->orderBy('price','ASC');
                break;
                case 'price_desc':
                $data['products'] = $data['products']->orderBy('price','DESC');
                break;
            }
            }
            $data['products_count_total']   = $data['products']->count();
            $data['products']               = ($limit==0 && $offset==0)?$data['products']:$data['products']->limit($limit)->offset($offset);
            $data['products']               = $data['products']->get();
            $data['products_count_start']   = ($data['products_count_total'] == 0 ? 0 : (($page-1)*$limit)+1);
            $data['products_count_end']     = ($data['products_count_total'] == 0 ? 0 : (($page-1)*$limit)+sizeof($data['products']));
           return response()->json($data, 200);

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }

    /**
    * @OA\Post(
    *     path="/api/bed",
    *     tags={"Bed"},
    *     summary="Store a newly created item",
    *     operationId="store",
    *     @OA\Response(
    *         response=400,
    *         description="Invalid input",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(
    *         response=201,
    *         description="Successful",
    *         @OA\JsonContent()
    *     ),
    *     @OA\RequestBody(
    *         required=true,
    *         description="Request body description",
    *         @OA\JsonContent(
    *             ref="#/components/schemas/Bed",
    *             example={"product_name": "kursi 160x200 ","brand": "Informa","type": "Kasur","cover": "https://res.cloudinary.com/ruparupa-com/image/upload/l_2.1:c5f05d:8cb583/w_738,h_738/f_auto,q_auto:eco/v1690873564/Products/10327678_3.jpg","description": "Struktur per pada kasur dirancang agar memberi area tidur yang nyaman dan independen saat digunakan oleh dua orang. Beban pun akan tersebar ke seluruh sisi kasur, tanpa menimbulkan bunyi berisik dan per akan dengan mudah kembali ke posisi semula. Kasur ini juga dilengkapi bantal dan guling berbahan dasar chopped memory foam dengan cover yang bisa dilepas dan dicuci. Memerhatikan unsur higienitas dan efisiensi, kasur beserta bantal dan guling akan divakum dan dikemas dalam kotak.","price": 3099000}
    *         ),
    *     ),
    *     security={{"passport_token_ready":{},"passport":{}}}
    * )
    */

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'product_name'  => 'required|unique:beds',
                'brand'  => 'required|max:100',
            ]); 
            if ($validator->fails()) {
                throw new HttpException(400, $validator->messages()->first());
            }
            $bed = new Bed;
            $bed->fill($request->all())->save();
            return $bed;

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }


    /**
    * @OA\Get(
    *     path="/api/bed/{id}",
    *     tags={"Bed"},
    *     summary="Display the specified item",
    *     operationId="show",
    *     @OA\Response(
    *         response=404,
    *         description="Item not found",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(
    *         response=400,
    *         description="Invalid input",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Successful",
    *         @OA\JsonContent()
    *     ),
    *     @OA\Parameter(
    *         name="id",
    *         in="path",
    *         description="ID of item that needs to be displayed",
    *         required=true,
    *         @OA\Schema(
    *             type="integer",
    *             format="int64"
    *         )
    *     ),
    * )
    */
    
    
    public function show($id)
    {
        $bed = Bed::find($id);
        if(!$bed){
            throw new HttpException(404, 'Item not found');
        }
        
        return $bed;
    }

    /**
     * @OA\Put(
     *     path="/api/bed/{id}",
     *     tags={"Bed"},
     *     summary="Update the specified item",
     *     operationId="update",
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="invalid input",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of item that needs to be updated",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body description",
     *         @OA\JsonContent(
     *             ref="#/components/schemas/Bed",
     *             example={"product_name": "kursi 160x200 ","brand": "Informa","type": "Kasur","cover": "https://res.cloudinary.com/ruparupa-com/image/upload/l_2.1:c5f05d:8cb583/w_738,h_738/f_auto,q_auto:eco/v1690873564/Products/10327678_3.jpg","description": "Struktur per pada kasur dirancang agar memberi area tidur yang nyaman dan independen saat digunakan oleh dua orang. Beban pun akan tersebar ke seluruh sisi kasur, tanpa menimbulkan bunyi berisik dan per akan dengan mudah kembali ke posisi semula. Kasur ini juga dilengkapi bantal dan guling berbahan dasar chopped memory foam dengan cover yang bisa dilepas dan dicuci. Memerhatikan unsur higienitas dan efisiensi, kasur beserta bantal dan guling akan divakum dan dikemas dalam kotak.","price": 3099000}
     *         ),
     *     ),
     *     security={{"passport_token_ready":{},"passport":{}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $bed = Bed::find($id);
        if(!$bed){
            throw new HttpException(404, 'Item not found');
        }

        try {
            $validator = Validator::make($request->all(), [
                'product_name'  => 'required|unique:beds',
                'brand'  => 'required|max:100',
            ]); 
            if ($validator->fails()) {
                throw new HttpException(400, $validator->messages()->first());
            }
           $bed->fill($request->all())->save();
           return response()->json(array('message'=>'Updated successfully'), 200);

        } catch(\Exception $exception) {
           throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }
    
    /**
     * @OA\Delete(
     *     path="/api/bed/{id}",
     *     tags={"Bed"},
     *     summary="Remove the specified item",
     *     operationId="destroy",
     *     @OA\Response(
     *         response=404,
     *         description="Item not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of item that needs to be removed",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     security={{"passport_token_ready":{},"passport":{}}}
     * )
     */
    public function destroy($id)
    {
        $bed = Bed::find($id);
        if(!$bed){
            throw new HttpException(404, 'Item not found');
        }

        try {
            $bed->delete();
            return response()->json(array('message'=>'Deleted successfully'), 200);

        } catch(\Exception $exception) {
            throw new HttpException(400, "Invalid data : {$exception->getMessage()}");
        }
    }
}
    //

