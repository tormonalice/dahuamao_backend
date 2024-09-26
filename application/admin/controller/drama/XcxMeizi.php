<?php

namespace app\admin\controller\drama;

use addons\drama\library\Wechat;
use addons\drama\model\Config;
use app\admin\library\Auth;
use app\admin\model\drama\VideoPerformer;
use app\admin\model\Sites;
use app\common\controller\Backend;
use EasyWeChat\Kernel\Exceptions\HttpException;
use fast\Http;
use GuzzleHttp\Client;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;
use think\Db;
use think\db\exception\BindParamException;
use think\exception\PDOException;
use think\exception\ValidateException;
use Exception;
use think\Queue;
use think\Response;

/**
 * 短剧
 *
 * @icon fa fa-circle-o
 */
class XcxMeizi extends Backend
{

    /**
     * Video模型对象
     * @var \app\admin\model\drama\Video
     */
    protected $dataLimit = 'auth';
    protected $dataLimitField = 'site_id';
    protected $noNeedRight = ['changePrice', 'sync', 'sync_add', 'test_data_video', 'test_data_category', 'download', 'import', 'icon', 'episodes','tbXcx','meizi','xiangqing','jumu'];
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\drama\Video;
        $this->assignconfig("flagsList", $this->model->getFlagsList());
        $this->assignconfig("categoryList", $this->model->getCategoryList($this->auth->id));
        $this->assignconfig("yearList", $this->model->getYearList($this->auth->id));
        $this->assignconfig("areaList", $this->model->getAreaList($this->auth->id));
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            // list($where, $sort, $order, $offset, $limit) = $this->buildparams('title');
            $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
            $order = $this->request->get("order", "DESC");
            $offset = $this->request->get("offset", 0);
            $limit = $this->request->get("limit", 0);

            $total = $this->buildSearchOrder()->count();
            // 构建查询数据条件
            $list = $this->buildSearchOrder();

            $goodsTableName = $this->model->getQuery()->getTable();

            $list = $list->field("$goodsTableName.*")
                ->orderRaw($sort . ' ' . $order)
                ->limit($offset, $limit)
                ->select();

            foreach ($list as $row) {
                $row->hidden(['site_id', 'content', 'fake_views', 'fake_favorites', 'fake_shares', 'deletetime', 'createtime']);
                $row->flags_text_arr = $row->flags_text ? explode(',', $row->flags_text) : [];
                $row->category_text_arr = explode(',', $row->category_text);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);

            if ($this->request->get("page_type") == 'select') {
                return json($result);
            }

            return $this->success('操作成功', null, $result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $other = $this->request->post("other/a");

            if ($params) {
                $params = $this->preExcludeFields($params);

                if (!is_numeric($params['price']) || !is_numeric($params['vprice'])) {
                    $this->error("请填写正确的价格");
                }
                if ($params['price'] < $params['vprice']) {
                    $this->error('会员价格必须小于非会员价格！');
                }
                if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
                    $params[$this->dataLimitField] = $this->auth->id;
                }
                $result = false;
                Db::startTrans();
                try {
                    $result = $this->model->validateFailException(true)->validate('\app\admin\validate\drama\Video.add')->allowField(true)->save($params);
                    if ($result) {
                        $this->editOther($this->model, $other, 'add');
                        Db::commit();
                    }

                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success("添加成功");
                } else {
                    $this->error(__('No rows were inserted'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }


    /**
     * 查看详情
     */
    public function detail($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $row->append(['category_ids_arr']);
        $result = [];
        $result['videoPerformer'] = \app\admin\model\drama\VideoPerformer::where(['site_id' => $this->auth->id, 'vid' => $ids])
            ->order('weigh desc, id asc')->select();
        $result['videoEpisodes'] = \app\admin\model\drama\VideoEpisodes::where(['site_id' => $this->auth->id, 'vid' => $ids])
            ->order('weigh desc, id asc')->select();

        $result['detail'] = $row;

        return $this->success('获取成功', null, $result);
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        if (!$ids) {
            $ids = $this->request->get('id');
        }
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $row->updatetime = time();

        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            if (!in_array($row[$this->dataLimitField], $adminIds)) {
                $this->error(__('You have no permission'));
            }
        }
        if ($this->request->isPost()) {
            $params = $this->request->post("row/a");
            $other = $this->request->post("other/a");

            if ($params) {
                $params = $this->preExcludeFields($params);
                if ($params['price'] < $params['vprice']) {
                    $this->error('会员价格必须小于非会员价格！');
                }
                $result = false;
                Db::startTrans();
                try {
                    $result = $row->validateFailException(true)->validate('\app\admin\validate\drama\Video.edit')->allowField(true)->save($params);
                    if ($result) {
                        $this->editOther($row, $other, 'edit');
                        Db::commit();
                    }
                } catch (ValidateException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (PDOException $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                } catch (Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                if ($result !== false) {
                    $this->success("编辑成功");
                } else {
                    $this->error(__('No rows were updated'));
                }
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $this->view->assign("row", $row);
        $videoPerformer = \app\admin\model\drama\VideoPerformer::where(['site_id' => $this->auth->id, 'vid' => $ids])
            ->order('weigh desc, id asc')->select();
        $videoEpisodes = \app\admin\model\drama\VideoEpisodes::where(['site_id' => $this->auth->id, 'vid' => $ids])
            ->order('weigh desc, id asc')->select();
        $this->assignconfig('videoPerformer', $videoPerformer);
        $this->assignconfig('videoEpisodes', $videoEpisodes);
        return $this->view->fetch();
    }


    /**
     * 真实删除
     *
     * @param $ids
     * @return void
     */
    public function destroy($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__("Invalid parameters"));
        }
        $ids = $ids ?: $this->request->post('ids');
        $pk = $this->model->getPk();
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds)) {
            $this->model->where($this->dataLimitField, 'in', $adminIds);
        }
        if ($ids) {
            $this->model->where($pk, 'in', $ids);
        }
        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->onlyTrashed()->select();
            foreach ($list as $item) {
                // 删除演员和剧集
                \app\admin\model\drama\VideoPerformer::where('vid', $item->id)->delete();
                \app\admin\model\drama\VideoEpisodes::where('vid', $item->id)->delete();
                $count += $item->delete(true);
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

    /**
     * 修改短剧状态
     * @param $ids
     * @param $status
     */
    public function setStatus($ids, $status)
    {
        if ($ids) {
            $pk = $this->model->getPk();
            $adminIds = $this->getDataLimitAdminIds();
            if (is_array($adminIds)) {
                $this->model->where($this->dataLimitField, 'in', $adminIds);
            }
            $list = $this->model->where($pk, 'in', $ids)->select();

            $count = 0;
            Db::startTrans();
            try {
                foreach ($list as $k => $v) {
                    $v->status = $status;
                    $count += $v->save();
                }
                Db::commit();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
            if ($count) {
                $this->success();
            } else {
                $this->error(__('No rows were updated'));
            }
        }
        $this->error(__('Parameter %s can not be empty', 'ids'));
    }

    public function changePrice(){
        $params = $this->request->post();
        $where[$this->dataLimitField] = $this->auth->id;
        $video_list = \app\admin\model\drama\Video::withTrashed()->where($where)->select();
        Db::startTrans();
        foreach ($video_list as &$video){
            try {
                if(isset($params['vpriceFreeChecked']) && $params['vpriceFreeChecked'] === "true"){
                    $video->vprice = 0;
                }elseif (isset($params['video_vprice']) && $params['video_vprice'] !== ''){
                    $video->vprice = $params['video_vprice'];
                }
                if(isset($params['video_price']) && $params['video_price'] !== ''){
                    $video->price = $params['video_price'];
                }
                $episode_list = \app\admin\model\drama\VideoEpisodes::withTrashed()
                    ->where('vid', $video['id'])
                    ->where($where)
                    ->orderRaw('weigh desc, id asc')
                    ->select();
                $i = 0;
                foreach ($episode_list as &$episode){
                    ++$i;
                    // vip 价格
                    if(isset($params['vpriceFreeChecked']) && $params['vpriceFreeChecked'] === "true"){
                        $episode->vprice = 0;
                    }elseif (isset($params['episode_vprice']) &&
                        isset($params['episode_top_vip']) &&
                        $params['episode_vprice'] !== '' &&
                        $params['episode_top_vip'] >= $i){
                        $episode->vprice = $params['episode_vprice'];
                    }elseif (isset($params['episode_vprice_last']) &&
                        isset($params['episode_top_vip']) &&
                        $params['episode_vprice_last'] !== '' &&
                        $params['episode_top_vip'] < $i){
                        $episode->vprice = $params['episode_vprice_last'];
                    }

                    // 普通价格
                    if (isset($params['episode_price']) &&
                        isset($params['episode_top']) &&
                        $params['episode_price'] !== '' &&
                        $params['episode_top'] >= $i){
                        $episode->price = $params['episode_price'];
                    }elseif (isset($params['episode_price_last']) &&
                        isset($params['episode_top']) &&
                        $params['episode_price_last'] !== '' &&
                        $params['episode_top'] < $i){
                        $episode->price = $params['episode_price_last'];
                    }
                    $episode->save();
                }
                $video->save();
            } catch (PDOException $e) {
                Db::rollback();
                $this->error($e->getMessage());
            } catch (Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }
        }
        Db::commit();
        $this->success('价格修改成功！');
    }

    /**
     * 下载模板文件
     */
    public function download()
    {
        $filename = "episodes.xls";
        $filePath = ADDON_PATH . "drama" . DS . 'library' . DS . 'data' . DS . $filename;
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            $this->error('文件不存在:' . $filePath);
        }
    }

    /**
     * 导入
     *
     * @return void
     * @throws PDOException
     * @throws BindParamException
     */
    public function import()
    {
        $file = $this->request->post('url');
        if (!$file) {
            $this->error(__('Parameter %s can not be empty', 'file'));
        }
        $filePath = ROOT_PATH . 'public' . $file;
        if (!is_file($filePath)) {
            $file_url = cdnurl($file, true);
            $this->downloadurl($file_url, $filePath);
            if (!is_file($filePath)) {
                $this->error(__('No Results were found'));
            }
        }
        //实例化reader
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        if (!in_array($ext, ['csv', 'xls', 'xlsx'])) {
            $this->error(__('Unknown data format'));
        }
        if ($ext === 'csv') {
            $file = fopen($filePath, 'r');
            $filePath = tempnam(sys_get_temp_dir(), 'import_csv');
            $fp = fopen($filePath, 'w');
            $n = 0;
            while ($line = fgets($file)) {
                $line = rtrim($line, "\n\r\0");
                $encoding = mb_detect_encoding($line, ['utf-8', 'gbk', 'latin1', 'big5']);
                if ($encoding !== 'utf-8') {
                    $line = mb_convert_encoding($line, 'utf-8', $encoding);
                }
                if ($n == 0 || preg_match('/^".*"$/', $line)) {
                    fwrite($fp, $line . "\n");
                } else {
                    fwrite($fp, '"' . str_replace(['"', ','], ['""', '","'], $line) . "\"\n");
                }
                $n++;
            }
            fclose($file) || fclose($fp);

            $reader = new Csv();
        } elseif ($ext === 'xls') {
            $reader = new Xls();
        } else {
            $reader = new Xlsx();
        }

        //导入文件首行类型,默认是注释,如果需要使用字段名称请使用name
        $importHeadType = isset($this->importHeadType) ? $this->importHeadType : 'comment';

        // $table = $this->model->getQuery()->getTable();
        $table = 'vs_drama_video_episodes';
        $database = \think\Config::get('database.database');
        $fieldArr = [];
        $list = db()->query("SELECT COLUMN_NAME,COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ? AND TABLE_SCHEMA = ?", [$table, $database]);
        foreach ($list as $k => $v) {
            if ($importHeadType == 'comment') {
                $v['COLUMN_COMMENT'] = explode(':', $v['COLUMN_COMMENT'])[0]; //字段备注有:时截取
                $fieldArr[$v['COLUMN_COMMENT']] = $v['COLUMN_NAME'];
            } else {
                $fieldArr[$v['COLUMN_NAME']] = $v['COLUMN_NAME'];
            }
        }

        //加载文件
        $insert = [];
        try {
            if (!$PHPExcel = $reader->load($filePath)) {
                $this->error(__('Unknown data format'));
            }
            $currentSheet = $PHPExcel->getSheet(0);  //读取文件中的第一个工作表
            $allColumn = $currentSheet->getHighestDataColumn(); //取得最大的列号
            $allRow = $currentSheet->getHighestRow(); //取得一共有多少行
            $maxColumnNumber = Coordinate::columnIndexFromString($allColumn);
            $fields = [];
            for ($currentRow = 1; $currentRow <= 1; $currentRow++) {
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $fields[] = $val;
                }
            }

            for ($currentRow = 2; $currentRow <= $allRow; $currentRow++) {
                $values = [];
                for ($currentColumn = 1; $currentColumn <= $maxColumnNumber; $currentColumn++) {
                    $val = $currentSheet->getCellByColumnAndRow($currentColumn, $currentRow)->getValue();
                    $values[] = is_null($val) ? '' : $val;
                }
                $row = [];
                $temp = array_combine($fields, $values);
                foreach ($temp as $k => $v) {
                    if (isset($fieldArr[$k]) && $k !== '') {
                        if ($fieldArr[$k] == 'status') {
                            $v = $v == '显示' ? 'normal' : 'hidden';
                        }
                        if ($fieldArr[$k] == 'video') {
                            // 视频时长无法直接获取，需要插件扩展
                        }
                        $row[$fieldArr[$k]] = $v;
                    }
                }
                if ($row) {
                    $row[$this->dataLimitField] = $this->auth->id;
                    $insert[] = $row;
                }
            }
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
        if (!$insert) {
            $this->error(__('No rows were updated'));
        }

        try {
            //是否包含admin_id字段
            $has_admin_id = false;
            foreach ($fieldArr as $name => $key) {
                if ($key == 'admin_id') {
                    $has_admin_id = true;
                    break;
                }
            }
            if ($has_admin_id) {
                $auth = Auth::instance();
                foreach ($insert as &$val) {
                    if (!isset($val['admin_id']) || empty($val['admin_id'])) {
                        $val['admin_id'] = $auth->isLogin() ? $auth->id : 0;
                    }
                }
            }
            (new \app\admin\model\drama\VideoEpisodes())->saveAll($insert);
        } catch (PDOException $exception) {
            $msg = $exception->getMessage();
            if (preg_match("/.+Integrity constraint violation: 1062 Duplicate entry '(.+)' for key '(.+)'/is", $msg, $matches)) {
                $msg = "导入失败，包含【{$matches[1]}】的记录已存在";
            };
            $this->error($msg);
        } catch (Exception $e) {
            $this->error($e->getMessage());
        }

        $this->success('数据导入成功');
    }

    /**
     * 远程下载
     */
    private function downloadurl($url, $filePath)
    {
        $contentType = '';
        try {
            $client = new Client();
            $response = $client->request('GET', $url, ['stream' => true, 'verify' => false, 'allow_redirects' => ['strict' => true]]);
            $body = $response->getBody();
            $fileData = $body->getContents();
        } catch (\Exception $e) {
            $this->error("下载失败");
        }
        file_put_contents($filePath, $fileData);
        return;
    }

    public function episodes($ids){
        $site_info = Db::name('sites')->where('site_id', $this->auth->id)->find();
        if($site_info['is_default'] == 1 || $site_info['domain']){
            $h5 = '/h5/';
        }else{
            $h5 = '/h5/?'.$site_info['sign'];
        }
        // 链接
        $url = request()->domain().$h5.'#/pages/video/play?id='.$ids;
        // 小程序码
        
        $this->success('', null, ['url'=>$url]);
    }

    /**
     * 导入测试数据
     * @throws PDOException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function sync()
    {
        // 导入分类数据
        Db::startTrans();
        // 清空数据
        Db::name('drama_category')->where('site_id', $this->auth->id)
            ->where('type', 'in', ['video', 'year', 'area'])->delete();
        Db::name('drama_video')->where('site_id', $this->auth->id)->delete();
        Db::name('drama_video_episodes')->where('site_id', $this->auth->id)->delete();
        Db::name('drama_video_performer')->where('site_id', $this->auth->id)->delete();
        $testdata_category = ADDON_PATH . "drama" . DS . 'testdata' . DS . "category.php";
        if (is_file($testdata_category)) {
            $datas = include $testdata_category;
            foreach ($datas as $topic => $prompts) {
                $templine = str_ireplace('__PREFIX__', config('database.prefix'), $topic);
                $templine = str_ireplace('__SITEID__', $this->auth->id, $templine);
                $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                try {
                    $resulte = Db::execute($templine);
                    $topic_id = Db::getLastInsID();
                } catch (\PDOException $e) {
                    Db::rollback();
                    $this->error('测试数据导入错误：' . $e->getMessage());
                }
                if ($resulte !== false) {
                    foreach ($prompts as $prompt) {
                        $templine = str_ireplace('__PREFIX__', config('database.prefix'), $prompt);
                        $templine = str_ireplace('__SITEID__', $this->auth->id, $templine);
                        $templine = str_ireplace('__LASTINSERTID__', $topic_id, $templine);
                        $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                        try {
                            $resulte = Db::execute($templine);
                        } catch (\PDOException $e) {
                            Db::rollback();
                            $this->error('测试数据导入错误：' . $e->getMessage());
                        }
                    }
                }
            }
        } else {
            $this->error('数据文件不存在！');
        }

        $categorys = Db::name('drama_category')
            ->where(['site_id' => $this->auth->id])
            ->where('pid', '>', 0)
            ->where('type', 'in', ['video', 'year', 'area'])
            ->field('id, name, type')
            ->select();
        // 导入短剧数据
        $testdata_file = ADDON_PATH . "drama" . DS . 'testdata' . DS . "video.php";
        if (is_file($testdata_file)) {
            $datas = include $testdata_file;
            foreach ($datas as $video => $episodes) {
                $templine = str_ireplace('__PREFIX__', config('database.prefix'), $video);
                $templine = str_ireplace('__SITEID__', $this->auth->id, $templine);
                $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                foreach ($categorys as $category) {
                    if ($category['type'] == 'video') {
                        $templine = str_ireplace($category['name'], $category['id'], $templine);
                    } else {
                        $templine = str_ireplace("'" . $category['name'] . "'", $category['id'], $templine);
                    }
                }
                try {
                    $resulte = Db::execute($templine);
                    $video_id = Db::getLastInsID();
                } catch (\PDOException $e) {
                    Db::rollback();
                    $this->error('测试数据导入错误：' . $e->getMessage());
                }
                if ($resulte !== false) {
                    foreach ($episodes as $episode) {
                        $templine = str_ireplace('__PREFIX__', config('database.prefix'), $episode);
                        $templine = str_ireplace('__SITEID__', $this->auth->id, $templine);
                        $templine = str_ireplace('__LASTINSERTID__', $video_id, $templine);
                        $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                        try {
                            $resulte = Db::execute($templine);
                        } catch (\PDOException $e) {
                            Db::rollback();
                            $this->error('测试数据导入错误：' . $e->getMessage());
                        }
                    }
                }
            }
            Db::commit();
            $this->success('测试数据导入成功！');
        } else {
            Db::rollback();
            $this->error('短剧数据文件不存在！');
        }

    }


    public function sync_add()
    {
        $siteId = $this->auth->id;
        $result = json_decode($this->curlGet($siteId), true);
        if ($result['code'] == 0) {
            $this->error($result['msg']);
        }
        //先处理分类
        $cate = isset($result['data']['cate']) ? $result['data']['cate'] : "";
        $cateModel = new \addons\drama\model\Category();
        if (!empty($cate)) {
            foreach ($cate as $k => $v) {
                $v['site_id'] = $siteId;
                $check = \addons\drama\model\Category::where('name', $v['name'])->where('site_id', $siteId)->find();
                $oldId = $v['id'];
                if (!$check) {
                    Db::startTrans();
                    try {
                        unset($v['id']);
                        $createId = $cateModel::create($v, true);
                        $children = json_decode($this->curlGetChildren($oldId), true);
                        $cateChil = new \app\admin\model\drama\Category();
                        foreach ($children['data'] as $k1 => $v1) {
                            unset($v1['id']);
                            $v1['site_id'] = $siteId;
                            $v1['pid'] = $createId['id'];
                            $cateChil::create($v1, true);
                        }
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                }
            }
        }


        //在处理剧集
        $video = isset($result['data']['video']) ? $result['data']['video'] : "";
        $videoModel = new \addons\drama\model\Video();
        $epis = new \app\admin\model\drama\VideoEpisodes();
        if (!empty($video)) {
            foreach ($video as $k => $v) {
                $v['site_id'] = $siteId;
                $check = $videoModel::where('title', $v['title'])->where('site_id', $siteId)->find();
                $oldId = $v['id'];
                if (!$check) {
                    Db::startTrans();
                    try {
                        //处理一下剧集ID
                        $v['area_id'] = \app\admin\model\drama\Category::where('name', $v['area_text'])->where('site_id', $siteId)->value('id');
                        $v['year_id'] = \app\admin\model\drama\Category::where('name', $v['year_text'])->where('site_id', $siteId)->value('id');
                        $v['category_ids'] = $this->dealCate($v['category_text'],$siteId);
                        unset($v['id']);
                        $create = $videoModel::create($v, true);
                        $videoEpis = json_decode($this->curlGetEpis($oldId), true);
                        foreach ($videoEpis['data'] as $k1 => $v1) {
                            unset($v1['id']);
                            $v1['site_id'] = $siteId;
                            $v1['vid'] = $create['id'];
                            $epis::create($v1, true);
                        }
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                }
            }
        }

        //在处理演员
        $perofr = isset($result['data']['perofr']) ? $result['data']['perofr'] : "";
        $perofrModel = new VideoPerformer();
        if (!empty($perofr)) {
            foreach ($perofr as $k => $v) {
                $v['site_id'] = $siteId;
                $check = $perofrModel::where('name', $v['name'])->find();
                if (!$check) {
                    Db::startTrans();
                    try {
                        unset($v['id']);
                        $create = $perofrModel::create($v, true);
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                }
            }
        }
        $this->success('ok');
    }






    public function dealCate($text,$siteId)
    {
        $arrayText = array_filter(explode(',', $text));
        $str = "";
        $model = new \app\admin\model\drama\Category();
        foreach ($arrayText as $k => $v) {
            $id=$model->Where('name',$v)->where('site_id',$siteId)->value('id');
            $str.=$id.",";
        }
        return rtrim($str,',');
    }


    public function sync_add1()
    {
        Db::startTrans();
        // 导入短剧数据
        $testdata_file = ADDON_PATH . "drama" . DS . 'testdata' . DS . "video_add.php";
        if (is_file($testdata_file)) {
            $datas = include $testdata_file;
            foreach ($datas as $video => $episodes) {
                $templine = str_ireplace('__PREFIX__', config('database.prefix'), $video);
                $templine = str_ireplace('__SITEID__', $this->auth->id, $templine);
                $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                $category_id = Db::name('drama_category')
                    ->where(['site_id' => $this->auth->id])
                    ->where('pid', '>', 0)
                    ->where('type', 'video')
                    ->orderRaw('rand()')
                    ->value('id');
                $category_id = $category_id ?? 0;
                $templine = str_ireplace('__CATEGORY__', $category_id, $templine);
                $year_id = Db::name('drama_category')
                    ->where(['site_id' => $this->auth->id])
                    ->where('pid', '>', 0)
                    ->where('type', 'year')
                    ->orderRaw('rand()')
                    ->value('id');
                $year_id = $year_id ?? 0;
                $templine = str_ireplace('__YEAR__', $year_id, $templine);
                $area_id = Db::name('drama_category')
                    ->where(['site_id' => $this->auth->id])
                    ->where('pid', '>', 0)
                    ->where('type', 'area')
                    ->orderRaw('rand()')
                    ->value('id');
                $area_id = $area_id ?? 0;
                $templine = str_ireplace('__AREA__', $area_id, $templine);
                try {
                    $resulte = Db::execute($templine);
                    $video_id = Db::getLastInsID();
                } catch (\PDOException $e) {
                    Db::rollback();
                    $this->error('片源数据导入错误：' . $e->getMessage());
                }
                if ($resulte !== false) {
                    foreach ($episodes as $episode) {
                        $templine = str_ireplace('__PREFIX__', config('database.prefix'), $episode);
                        $templine = str_ireplace('__SITEID__', $this->auth->id, $templine);
                        $templine = str_ireplace('__LASTINSERTID__', $video_id, $templine);
                        $templine = str_ireplace('INSERT INTO ', 'INSERT IGNORE INTO ', $templine);
                        try {
                            $resulte = Db::execute($templine);
                        } catch (\PDOException $e) {
                            Db::rollback();
                            $this->error('片源数据导入错误：' . $e->getMessage());
                        }
                    }
                }
            }
            Db::commit();
            @unlink($testdata_file);
            $this->success('片源数据导入成功！');
        } else {
            Db::rollback();
            $this->error('片源数据文件不存在！');
        }

    }


    /**
     * 生成后缀图标
     */
    public function icon()
    {
        $suffix = $this->request->request("suffix");
        $suffix = $suffix ? $suffix : "FILE";
        $data = build_suffix_image($suffix);
        $header = ['Content-Type' => 'image/svg+xml'];
        $offset = 30 * 60 * 60 * 24; // 缓存一个月
        $header['Cache-Control'] = 'public';
        $header['Pragma'] = 'cache';
        $header['Expires'] = gmdate("D, d M Y H:i:s", time() + $offset) . " GMT";
        $response = Response::create($data, '', 200, $header);
        return $response;
    }

    public function test_data_category()
    {
        $category = Db::name('drama_category')->where('site_id', $this->auth->id)->where('pid', 0)->select();
        $column_category = Db::query('SELECT COLUMN_NAME,DATA_TYPE FROM information_schema.columns WHERE TABLE_NAME = "vs_drama_category"');
        $string_column_category = [];
        foreach ($column_category as $item) {
            if (in_array($item['DATA_TYPE'], ['varchar', 'set', 'text', 'enum'])) {
                $string_column_category[] = $item['COLUMN_NAME'];
            }
        }

        $data_category = [];
        foreach ($category as $item) {
            $type = $item['type'];
            $pid = $item['id'];
            $item['id'] = 'null';
            $item['site_id'] = '__SITEID__';
            $item['image'] = $item['image'] ? cdnurl($item['image'], true) : '';
            foreach ($string_column_category as $column) {
                $item[$column] = "'" . $item[$column] . "'";
            }
            $str = trim(implode(', ', $item), ', ');
            $str = 'INSERT INTO `vs_drama_category` VALUES (' . $str . ');';
            $data_category[$str] = [];
            $categorys = Db::name('drama_category')
                ->where('site_id', $this->auth->id)
                ->where('pid', $pid)
                ->where('type', $type)
                ->order('weigh desc, id asc')
                ->select();
            foreach ($categorys as &$value) {
                $value['id'] = 'null';
                $value['site_id'] = '__SITEID__';
                $value['pid'] = '__LASTINSERTID__';
                $value['image'] = $value['image'] ? cdnurl($value['image'], true) : '';
                foreach ($string_column_category as $column) {
                    $value[$column] = "'" . $value[$column] . "'";
                }
                $str_category = trim(implode(', ', $value), ', ');
                $str_category = 'INSERT INTO `vs_drama_category` VALUES (' . $str_category . ');';
                $data_category[$str][] = $str_category;
            }
        }
        $testdata_file = ADDON_PATH . "drama" . DS . 'testdata' . DS . "category.php";
        file_put_contents(
            $testdata_file,
            '<?php' . "\n\nreturn " . var_export($data_category, true) . ";"
        );

        var_dump($data_category);
        die();
    }

    public function test_data_video()
    {
        $video = Db::name('drama_video')->where('site_id', $this->auth->id)->where('id', '>', 70)->select();
        $column_video = Db::query('SELECT COLUMN_NAME,DATA_TYPE FROM information_schema.columns WHERE TABLE_NAME = "vs_drama_video"');
        $string_column_video = [];
        foreach ($column_video as $item) {
            if (in_array($item['DATA_TYPE'], ['varchar', 'set', 'text', 'enum'])) {
                $string_column_video[] = $item['COLUMN_NAME'];
            }
        }
        $column_video_episodes = Db::query('SELECT COLUMN_NAME,DATA_TYPE FROM information_schema.columns WHERE TABLE_NAME = "vs_drama_video_episodes"');
        $string_column_video_episodes = [];
        foreach ($column_video_episodes as $item) {
            if (in_array($item['DATA_TYPE'], ['varchar', 'set', 'text', 'enum'])) {
                $string_column_video_episodes[] = $item['COLUMN_NAME'];
            }
        }
        $column_video_performer = Db::query('SELECT COLUMN_NAME,DATA_TYPE FROM information_schema.columns WHERE TABLE_NAME = "vs_drama_video_performer"');
        $string_column_video_performer = [];
        foreach ($column_video_performer as $item) {
            if (in_array($item['DATA_TYPE'], ['varchar', 'set', 'text', 'enum'])) {
                $string_column_video_performer[] = $item['COLUMN_NAME'];
            }
        }
        $data_video = [];
        foreach ($video as $item) {
            $vid = $item['id'];
            $item['id'] = 'null';
            $item['site_id'] = '__SITEID__';
            $item['image'] = $item['image'] ? cdnurl($item['image'], true) : '';
            $item['deletetime'] = 'null';
            // $category_ids = explode(',', $item['category_ids']);
            // $catagery = Db::name('drama_category')->where('id', 'in', $category_ids)->column('name');
            // $item['category_ids'] = implode(',', $catagery);
            $item['category_ids'] = '__CATEGORY__';
            // $catagery_year = Db::name('drama_category')->where('id', $item['year_id'])->value('name');
            // $item['year_id'] = "'".$catagery_year."'";
            $item['year_id'] = '__YEAR__';
            // $catagery_area = Db::name('drama_category')->where('id', $item['area_id'])->value('name');
            // $item['area_id'] = "'".$catagery_area."'";
            $item['area_id'] = '__AREA__';
            foreach ($string_column_video as $column) {
                $item[$column] = "'" . $item[$column] . "'";
            }
            $str = trim(implode(', ', $item), ', ');
            $str = 'INSERT INTO `vs_drama_video` VALUES (' . $str . ');';
            $data_video[$str] = [];
            $episodes = Db::name('drama_video_episodes')->where('vid', $vid)->order('weigh desc, id asc')->select();
            foreach ($episodes as &$episode) {
                $episode['id'] = 'null';
                $episode['site_id'] = '__SITEID__';
                $episode['vid'] = '__LASTINSERTID__';
                $episode['image'] = $episode['image'] ? cdnurl($episode['image'], true) : '';
                $episode['video'] = $episode['video'] ? cdnurl($episode['video'], true) : '';
                $episode['deletetime'] = 'null';
                foreach ($string_column_video_episodes as $column) {
                    $episode[$column] = "'" . $episode[$column] . "'";
                }
                $str_episode = trim(implode(', ', $episode), ', ');
                $str_episode = 'INSERT INTO `vs_drama_video_episodes` VALUES (' . $str_episode . ');';
                $data_video[$str][] = $str_episode;
            }
            $performers = Db::name('drama_video_performer')->where('vid', $vid)->order('weigh desc, id asc')->select();
            foreach ($performers as &$performer) {
                $performer['id'] = 'null';
                $performer['site_id'] = '__SITEID__';
                $performer['vid'] = '__LASTINSERTID__';
                $performer['avatar'] = $performer['avatar'] ? cdnurl($performer['avatar'], true) : '';
                foreach ($string_column_video_performer as $column) {
                    $performer[$column] = "'" . $performer[$column] . "'";
                }
                $str_performer = trim(implode(', ', $performer), ', ');
                $str_performer = 'INSERT INTO `vs_drama_video_performer` VALUES (' . $str_performer . ');';
                $data_video[$str][] = $str_performer;
            }
        }
        // $testdata_file = ADDON_PATH . "drama" . DS . 'testdata' . DS . "video.php";
        $testdata_file = ADDON_PATH . "drama" . DS . 'testdata' . DS . "video_add.php";
        file_put_contents(
            $testdata_file,
            '<?php' . "\n\nreturn " . var_export($data_video, true) . ";"
        );

        var_dump($data_video);
        die();
    }

    protected function editOther($video, $other, $type = 'add')
    {
        $this->editPerformer($video, json_decode($other['performerData'], true), $type);
        $this->editEpisodes($video, json_decode($other['episodesData'], true), $type);
    }

    /**
     * 添加编辑演员
     */
    protected function editPerformer($video, $performer, $type = 'add')
    {
        if (count($performer) < 1) {
            if ($type == 'add') {
                return;
            }
            // throw new Exception('请填写演员列表');
        }
        foreach ($performer as $value) {
            if (empty($value['type'])) {
                throw new Exception('请填写演员类型');
            }
            if (empty($value['name'])) {
                throw new Exception('请填写演员姓名');
            }
            if (empty($value['avatar'])) {
                throw new Exception('请选择演员头像');
            }
        }
        if ($type == 'add') {
            // 创建新短剧，添加演员列表
            foreach ($performer as $s3 => &$k3) {
                $k3['site_id'] = $this->auth->id;
                $k3['vid'] = $video->id;
                $k3['createtime'] = time();

                unset($k3['id']);
                unset($k3['tags_arr']);
            }
            (new \app\admin\model\drama\VideoPerformer())->allowField(true)->saveAll($performer);
        } else {
            // 编辑旧演员，先删除老的不用的
            $oldPerformerIds = array_column($performer, 'id');
            // 删除当前短剧老的除了在基础上修改的
            \app\admin\model\drama\VideoPerformer::where('vid', $video->id)
                ->where('site_id', $this->auth->id)
                ->where('id', 'not in', $oldPerformerIds)
                ->delete();

            foreach ($performer as $s3 => $k3) {
                $data['site_id'] = $this->auth->id;
                $data['vid'] = $video->id;
                $data['type'] = $k3['type'];
                $data['name'] = $k3['name'];
                $data['en_name'] = $k3['en_name'];
                $data['avatar'] = $k3['avatar'];
                $data['tags'] = $k3['tags'];
                $data['play'] = $k3['play'];
                $data['weigh'] = $k3['weigh'];
                $data['profile'] = $k3['profile'];
                if ($k3['id']) {
                    // 编辑
                    $videoPerformer = \app\admin\model\drama\VideoPerformer::get($k3['id']);
                } else {
                    // 新增数据
                    $data['createtime'] = time();
                    $videoPerformer = new \app\admin\model\drama\VideoPerformer();
                }

                if ($videoPerformer) {
                    $videoPerformer->save($data);
                }
            }
        }
    }


    /**
     * 添加编辑剧集
     */
    protected function editEpisodes($video, $episodes, $type = 'add')
    {
        if (count($episodes) < 1) {
            if ($type == 'add') {
                return;
            }
            // throw new Exception('请填写剧集列表');
        }
        foreach ($episodes as $value) {
            if (empty($value['name'])) {
                throw new Exception('请填写剧集名称');
            }
            if (empty($value['video'])) {
                throw new Exception('请选择剧集视频');
            }
            if (!is_numeric($value['price']) || !is_numeric($value['vprice'])) {
                throw new Exception('请输入正确的价格');
            }
            if ($value['price'] < $value['vprice']) {
                throw new Exception('会员价格必须小于非会员价格！');
            }
            if ($video['price'] < $value['price']) {
                throw new Exception('整集价格必须大于单集价格！');
            }
            if ($video['vprice'] < $value['vprice']) {
                throw new Exception('整集VIP价格必须大于单集VIP价格！');
            }
        }
        $fake_likes = 0;
        $fake_views = 0;
        $fake_favorites = 0;
        $fake_shares = 0;
        if ($type == 'add') {
            // 创建新短剧，添加演员列表
            foreach ($episodes as $s2 => &$k2) {
                $k2['site_id'] = $this->auth->id;
                $k2['vid'] = $video->id;
                $k2['image'] = isset($k2['image']) && $k2['image'] ? $k2['image'] : $video['image'];
                $k2['updatetime'] = time();
                $k2['createtime'] = time();

                unset($k2['id']);
                unset($k2['suffix']);

                $fake_likes += $k2['fake_likes'] ? $k2['fake_likes'] : 0;
                $fake_views += $k2['fake_views'] ? $k2['fake_views'] : 0;
                $fake_favorites += $k2['fake_favorites'] ? $k2['fake_favorites'] : 0;
                $fake_shares += $k2['fake_shares'] ? $k2['fake_shares'] : 0;
            }
            (new \app\admin\model\drama\VideoEpisodes())->allowField(true)->saveAll($episodes);
        } else {
            // 编辑旧演员，先删除老的不用的
            $oldEpisodesIds = array_column($episodes, 'id');
            // 删除当前短剧老的除了在基础上修改的
            \app\admin\model\drama\VideoEpisodes::where('vid', $video->id)
                ->where('site_id', $this->auth->id)
                ->where('id', 'not in', $oldEpisodesIds)
                ->delete();

            foreach ($episodes as $s3 => $k3) {
                $data['site_id'] = $this->auth->id;
                $data['vid'] = $video->id;
                $data['name'] = $k3['name'];
                $data['image'] = isset($k3['image']) && $k3['image'] ? $k3['image'] : $video['image'];
                $data['video'] = $k3['video'];
                $data['duration'] = $k3['duration'];
                $data['price'] = $k3['price'];
                $data['vprice'] = $k3['vprice'];
                $data['fake_likes'] = $k3['fake_likes'] ?? 0;
                $data['fake_views'] = $k3['fake_views'] ?? 0;
                $data['fake_favorites'] = $k3['fake_favorites'] ?? 0;
                $data['fake_shares'] = $k3['fake_shares'] ?? 0;
                $data['weigh'] = $k3['weigh'];
                $data['status'] = $k3['status'];
                $data['updatetime'] = time();

                $fake_likes += $k3['fake_likes'] ? $k3['fake_likes'] : 0;
                $fake_views += $k3['fake_views'] ? $k3['fake_views'] : 0;
                $fake_favorites += $k3['fake_favorites'] ? $k3['fake_favorites'] : 0;
                $fake_shares += $k3['fake_shares'] ? $k3['fake_shares'] : 0;

                if ($k3['id']) {
                    // 编辑
                    $videoEpisodes = \app\admin\model\drama\VideoEpisodes::get($k3['id']);
                } else {
                    // 新增数据
                    $data['createtime'] = time();
                    $videoEpisodes = new \app\admin\model\drama\VideoEpisodes();
                }

                if ($videoEpisodes) {
                    $videoEpisodes->save($data);
                }
            }
        }
        $video->fake_likes = $fake_likes;
        $video->fake_views = $fake_views;
        $video->fake_favorites = $fake_favorites;
        $video->fake_shares = $fake_shares;
        $video->save();
    }

    // 构建查询条件
    private function buildSearchOrder()
    {
        $search = $this->request->get("search", '');        // 关键字
        $status = $this->request->get("status", 'all');
        $category_type = $this->request->get("category_type", 'all');
        $year_type = $this->request->get("year_type", 'all');
        $area_type = $this->request->get("area_type", 'all');
        $min_price = $this->request->get("min_price", "");
        $max_price = $this->request->get("max_price", "");
        $min_vprice = $this->request->get("min_vprice", "");
        $max_vprice = $this->request->get("max_vprice", "");

        $name = $this->model->getQuery()->getTable();
        $tableName = $name . '.';

        $video = $this->model->where($tableName . $this->dataLimitField, $this->auth->id);

        if ($search) {
            // 模糊搜索字段
            $searcharr = ['title', 'id'];
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }
            unset($v);
            $video = $video->where(function ($query) use ($searcharr, $search, $tableName) {
                $query->where(implode("|", $searcharr), "LIKE", "%{$search}%")
                    ->whereOrRaw("find_in_set('$search', " . $tableName . "tags)");
            });
        }

        if ($category_type != 'all') {
            $video = $video->whereRaw("find_in_set($category_type, " . $tableName . "category_ids)");
        }

        if ($year_type != 'all') {
            $video = $video->where($tableName . 'year_id', $year_type);
        }
        if ($area_type != 'all') {
            $video = $video->where($tableName . 'area_id', $area_type);
        }

        // 价格
        if ($min_price != '') {
            $video = $video->where('`price` >= ' . $min_price);
        }
        if ($max_price != '') {
            $video = $video->where('`price` <= ' . $max_price);
        }

        if ($min_vprice != '') {
            $video = $video->where('`vprice` >= ' . $min_vprice);
        }
        if ($max_vprice != '') {
            $video = $video->where('`vprice` <= ' . $max_vprice);
        }

        // 商品状态
        if ($status != 'all') {
            $video = $video->where('status', $status);
        }

        // if(isset($category_id) && $category_id != 0) {
        //     $category_ids = [];
        //     // 查询分类所有子分类,包括自己
        //     $category_ids = \addons\drama\model\Category::getCategoryIds($category_id);
        //
        //
        //     $video = $video->where(function ($query) use ($category_ids) {
        //         // 所有子分类使用 find_in_set or 匹配，亲测速度并不慢
        //         foreach($category_ids as $key => $category_id) {
        //             $category_id = filter_sql($category_id);
        //             $query->whereOrRaw("find_in_set($category_id, category_ids)");
        //         }
        //     });
        // }

        $video = $video->where('platform', 2);

        return $video;
    }

    //同步小程序媒资剧目id
    public function tbXcx(){

        /*
        $param = $this->request->param();

        if(!isset($param['ids'])){
            $this->error('参数错误');
        }

        $where = [];
        if($param['ids'] != ''){
            $where['id'] = ['in',$param['ids']];
        }


        $config = Config::where('name', 'wxMiniProgram')->where('site_id', $this->auth->id)->value('value');
        $config = json_decode($config, true);


        if(!isset($config['meizi_switch']) || !$config['meizi_switch']){
            $this->error('请现在系统配置-剧场配置-平台配置-微信小程序打开媒资');
        }
        */

        $time = time();

        //分批获取剧目
        $j = 0;
        while(true){

            $jumu = $this->model->getJumu($this->auth->id,50,$j);
            if($jumu['code'] == 0){
                //错误
                $this->error($jumu['msg']);
            }elseif(!isset($jumu['data']) || empty($jumu['data'])){
                //无信息
                break;
            }else{
                //审核通过的没有添加过的添加到数据库
                foreach($jumu['data'] as $v){
                    if($v['audit_detail']['status'] == 3){
                        $where['xcx_drama_id'] = $v['drama_id'];
                        $where['platform'] = 2;
                        $find = \app\admin\model\drama\Video::where($where)->find();
                        //数据库是否存在该剧
                        if(!$find){
                            $insert = [
                                'site_id' => $this->auth->id,
                                'title' => $v['name'],
                                'subtitle' => $v['name'],
                                'image' => $v['cover_url'],
                                'flags' => 'hot,recommend',
                                'description' => $v['name'],
                                'price' => 0,
                                'vprice' => 0,
                                'episodes' => $v['media_count'],
                                'score' => 5,
                                'fake_views' => rand(100000,900000),
                                'fake_favorites' => rand(10000,90000),
                                'fake_shares' => rand(10000,90000),
                                'fake_likes' => rand(10000,90000),
                                'status' => 'up',
                                'xcx_drama_id' => $v['drama_id'],
                                'platform' => 2,
                                'createtime' => $time,
                                'updatetime' => $time,
                            ];
                            //同步小程序媒资剧目id
                            \app\admin\model\drama\Video::insert($insert);
                        }
                    }
                }

                $j += 50;
            }

        }

        $this->success('同步成功，请打开计划任务同步剧集');

    }


}
