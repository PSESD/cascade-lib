<?php

namespace cascade\controllers;

use Yii;

use cascade\models\Registry;
use cascade\models\Relation;
use cascade\models\ObjectFamiliarity;
use cascade\models\DeleteForm;

use infinite\web\Controller;
use infinite\db\ActiveRecord;
use infinite\base\exceptions\HttpException;

use yii\web\AccessControl;
use yii\web\VerbFilter;


class ObjectController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'roles' => ['@'],
					],
					[
						'allow' => false,
						'roles' => ['?'],
					],
				],
			],
			'verbs' => [
				'class' => VerbFilter::className(),
				'actions' => [
					'index' => ['get'],
					'suggest' => ['get'],
					'view' => ['get'],
					'create' => ['get', 'post'],
					'setPrimary' => ['get'],
					'link' => ['get', 'post'],
					'setPrimary' => ['get'],
					'update' => ['get', 'post'],
					'delete' => ['get', 'post'],
					'watch' => ['get'],
					'unwatch' => ['get'],
					'widget' => ['get', 'post'],
					'unwatch' => ['get'],
				],
			],
		];
	}

	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			]
		];
	}

	public function actionIndex() {
		// echo Registry::parseModelAlias(\cascade\models\Group::modelAlias()) .'<br />';
		// echo '<hr />';
		// echo \cascade\modules\SectionContact\modules\TypePhoneNumber\models\ObjectPhoneNumber::modelAlias() .'<br />';
		// echo Registry::parseModelAlias(\cascade\modules\SectionContact\modules\TypePhoneNumber\models\ObjectPhoneNumber::modelAlias());
		// //echo "okay";
		// exit;
		//var_dump(Yii::$app->collectors['types']);
		return $this->render('index');
	}

	/**
	 *
	 */
	public function actionSuggest() {
		$package = array('results' => array());
		if (empty($_GET['modules']) or empty($_GET['term'])) {
			$this->json($package, true);
		}
		$ignore = array();
		if (!empty($_GET['ignore'])) {
			$ignore = $_GET['ignore'];
		}
		$modules = $_GET['modules'];
		$term = $_GET['term'];
		$scores = array();

		foreach ($modules as $module) {
			$moduleObject = Yii::$app->types->get($module);
			$results = array('name' => $moduleObject->title->getPlural(true), 'results' => array());
			$raw = $moduleObject->search($term, array('ignore' => $ignore));
			$results['results'] = array();
			if (!empty($raw['results'])) {
				foreach ($raw['results'] as $r) {
					$results['results'][] = array('objectId' => $r->primaryKey, 'label' => $r->descriptor, 'sub' => $r->getSubInfo($moduleObject->objectSubInfo), 'score' => $r->searchScore, 'module' => $moduleObject->shortName);
				}
			}
			if (!empty($results['results'])) {
				$package['results'][$module] = $results;
			}
		}
		$this->json($package, true);
	}


	/**
	 *
	 */
	public function actionView() {
		if (empty($_GET['id']) or !($object = $this->params['object'] = Registry::getObject($_GET['id'], true)) or !($typeItem = $this->params['typeItem'] = $object->objectTypeItem)) {
			throw new HttpException(404, "Unknown object.");
		}
		if (!$object->can('read')) {
			throw new HttpException(403, "Unable to access object.");
		}
		Yii::$app->request->object = $object;
		$this->response->view = 'view';

		$type = $this->params['type'] = $object->objectType;
		$sections = $this->params['sections'] = $typeItem->getSections($object);
		$this->params['active'] = $this->params['default'] = null;
		foreach ($sections as $section) {
			if ($section->displayPriority > 0) {
				$this->params['active'] = $this->params['default'] = $section->systemId;
				break;
			}
		}
		if (!empty($_GET['section'])) {
			$this->params['active'] = $_GET['section'];
		}
		ObjectFamiliarity::accessed($object);
	}



	protected function _parseParams($required = [], $can = null)
	{
		if (!empty($_GET['id']) && (!($this->params['object'] = Registry::getObject($_GET['id'], true)) || !($this->params['typeItem'] = $this->params['object']->objectTypeItem))) {
			throw new HttpException(404, "Unknown object.");
		}

		if (!empty($_GET['link']) && !($this->params['relation'] = Relation::get($_GET['relation_id']))) {
			throw new HttpException(404, "Unknown relationship.");
		}

		if (!empty($_GET['relation_id']) && !($this->params['relation'] = Relation::get($_GET['relation_id']))) {
			throw new HttpException(404, "Unknown relationship.");
		}

		if (isset($this->params['relation']) && isset($this->params['object'])) {
			if (!isset($_GET['object_relation']) || !in_array($_GET['object_relation'], ['child', 'parent'])) {
				throw new HttpException(400, "Invalid request object relation");
			}
			$this->params['relatedObject'] = $this->params['object'];
			if (!is_null($can) && !$this->params['relatedObject']->can($can)) {
				throw new HttpException(403, "You are not authorized to perform this action.");
			}
			$can = 'update';
			$this->params['object'] = null;
			if ($_GET['object_relation'] === 'child') {
				$this->params['modelBucket'] = 'children';
				$this->params['object'] = Registry::getObject($this->params['relation']->parent_object_id);
			} else {
				$this->params['modelBucket'] = 'parents';
				$this->params['object'] = Registry::getObject($this->params['relation']->child_object_id);
			}
			if (!$this->params['object']) {
				throw new HttpException(404, "Unknown object.");
			}
			$this->params['object']->tabularId = ActiveRecord::getPrimaryTabularId();
			$this->params['object']->_moduleHandler = ActiveRecord::FORM_PRIMARY_MODEL;

			$this->params['subform'] = $_GET['object_relation'] . ':'. $this->params['relatedObject']->objectType->systemId;
			$this->params['relatedObject']->tabularId = $this->params['relatedObject']->id;
			$this->params['relation']->tabularId = $this->params['relatedObject']->id;
			$this->params['relatedObject']->_moduleHandler = $this->params['subform'];
			$this->params['relatedObject']->registerRelationModel($this->params['relation']);

			if (isset($this->params['relatedObject']) && (!($this->params['relatedTypeItem'] = $this->params['relatedObject']->objectTypeItem) || !($this->params['relatedType'] = $this->params['relatedTypeItem']->object))) {
				throw new HttpException(404, "Unknown object type.");
			}
		}

		if (isset($this->params['object']) && (!($this->params['typeItem'] = $this->params['object']->objectTypeItem) || !($this->params['type'] = $this->params['typeItem']->object))) {
			throw new HttpException(404, "Unknown object type.");
		}

		if (!isset($this->params['handler']) && isset($this->params['object'])) {
			$this->params['handler'] = $this->params['type'];
		}

		if (!is_null($can) && isset($this->params['object']) && !$this->params['object']->can($can)) {
			throw new HttpException(403, "You are not authorized to perform this action.");
		}

		if (isset($_GET['subaction'])) {
			$this->params['subaction'] = $_GET['subaction'];
		}

		foreach ($required as $r) {
			if (!isset($this->params[$r])) {
				throw new HttpException(400, "Invalid request");
			}
		}
	}
	/**
	 *
	 */
	public function actionCreate() {
		if (!isset($_GET['type'])) { $_GET['type'] = ''; }
		$typeParsed = $originalTypeParsed= $_GET['type'];
		$subform = $object = null;
		$action = 'create';
		$saveSettings = [];

		$linkExisting = !empty($_GET['link']);
		if ($linkExisting) {
			$action = 'link';
		}

		if (!empty($_GET['object_id']) && (!($object = $this->params['object'] = Registry::getObject($_GET['object_id'], true)) || !($typeItem = $this->params['typeItem'] = $object->objectTypeItem))) {
			throw new HttpException(404, "Unknown object.");
		} elseif(isset($object)) {
			if (!$object->can('update')) {
				throw new HttpException(403, "Unable to update object.");
			}
			$typeParsedParts = explode(':', $typeParsed);
			if (count($typeParsedParts) >= 2 && in_array($typeParsedParts[0], ['parent', 'child'])) {
				$relationshipObjectField = $typeParsedParts[0] . '_object_id';
				$typeParsed = $typeParsedParts[1];
			} else {
				throw new HttpException(403, "Invalid request ");
			}
			$subform = implode(':', $typeParsedParts);
			$subformRelation = $originalTypeParsed;
			$saveSettings['allowEmpty'] = true;
		}

		if (empty($typeParsed) || !($type = Yii::$app->collectors['types']->getOne($typeParsed)) || !isset($type->object)) {
			throw new HttpException(404, "Unknown object type ". $typeParsed);
		}
		$module = $type->object;
		if (!Yii::$app->gk->canGeneral('create', $module->primaryModel)) {
			throw new HttpException(403, "You do not have access to create {$module->title->getPlural(true)}");
		}

		$this->response->view = 'create';
		$this->response->task = 'dialog';
		$this->response->taskOptions = array('title' => ucfirst($action) . ' '.$module->title->getSingular(true) , 'width' => '800px');

		if (isset($object)) {
			$module = $object->objectType;
		}

		$models = false;
		if (!empty($_POST)) {
			list($error, $notice, $models, $niceModels) = $module->handleSaveAll(null, $saveSettings);
			if ($error) {
				$this->response->error = $error;
			} else {
				$this->response->task = 'status';
				$noticeExtra = '';
				if (!empty($notice)) {
					$noticeExtra = ' However, there were notices: '. $notice;
				}
				$this->response->success = '<em>'. $niceModels['primary']['model']->descriptor .'</em> was saved successfully.'.$noticeExtra;
				if (isset($subform)) {
					$primaryModel = $type->object->primaryModel;
					$this->response->trigger = [
						['refresh', '.model-'. $primaryModel::baseClassName()]
					];
				} else {
					$this->response->redirect = $niceModels['primary']['model']->getUrl('view');
				}
			}
		}
		if ($models === false) {
			$models = $module->getModels($object);
		}
		if (!($this->params['form'] = $module->getForm($models, ['subform' => $subform, 'linkExisting' => $linkExisting]))) {
			throw new HttpException(403, "There is nothing to {$action} for {$module->title->getPlural(true)}");
		}
		$this->params['form']->ajax = true;
	}

	/**
	 *
	 */
	public function actionUpdate() {
		$subform = null;
		$this->_parseParams(['object', 'type'], 'update');
		extract($this->params);
		if (isset($relatedType)) {
			$primaryModel = $relatedType->primaryModel;
		} else {
			$primaryModel = $type->primaryModel;
		}
		if (isset($subaction) && $subaction === 'setPrimary') {
			if (!isset($relation)) {
				throw new HttpException(404, "Invalid relationship!");
			}
			$this->response->task = 'status';
			if ($relation->setPrimary()) {
				$this->response->trigger = [
					['refresh', '.model-'. $primaryModel::baseClassName()]
				];
			} else {
				$this->response->error = 'Unable to set primary object!';
			}
			return;
		} else {
			$this->response->view = 'create';
			$this->response->task = 'dialog';
			$this->response->taskOptions = array('title' => 'Update '. $type->title->getSingular(true));
			$models = $type->getModels($object, [$relatedObject->tabularId => $relatedObject, 'relations' => [$relatedObject->tabularId => $relation]]);

			if (!empty($_POST)) {
				list($error, $notice, $models, $niceModels) = $handler->handleSaveAll(null, ['allowEmpty' => true]);
				if ($error) {
					$this->response->error = $error;
				} else {
					$noticeExtra = '';
					if (!empty($notice)) {
						$noticeExtra = ' However, there were notices: '. $notice;
					}
					$this->response->success = '<em>'. $niceModels['primary']['model']->descriptor .'</em> was saved successfully.'.$noticeExtra;
					if (isset($relation)) {
						$this->response->trigger = [
							['refresh', '.model-'. $primaryModel::baseClassName()]
						];
						$this->response->task = 'status';
					} else {
						$this->response->redirect = $niceModels['primary']['model']->getUrl('view');
					}
				}
			}
			
			if (!($this->params['form'] = $handler->getForm($models, ['subform' => $subform, 'linkExisting' => false]))) {
				throw new HttpException(403, "There is nothing to update for {$type->title->getPlural(true)}");
			}
		}
	}

	/**
	 *
	 */
	public function actionDelete() {
		$subform = null;
		$this->_parseParams(['object', 'type'], 'delete');
		extract($this->params);
		if (isset($relatedType)) {
			$primaryModel = $relatedType->primaryModel;
		} else {
			$primaryModel = $type->primaryModel;
		}

		$this->response->view = 'delete';
		$this->response->task = 'dialog';
		$this->response->taskOptions = array('title' => 'Delete '. $type->title->getSingular(true) , 'isConfirmDeletion' => true);

		$this->params['model'] = new DeleteForm;
		$this->params['model']->object = $relatedObject;
		if (isset($relation)) {
			$this->params['model']->relationModel = $relation;
			$this->params['model']->relationshipWith = $object;
			$this->params['model']->forceObjectDelete = !$object->allowRogue($relation);
			if (!$this->params['model']->forceObjectDelete) {
				$this->params['model']->target = 'relationship';
			}
		} else {
			$this->params['model']->forceObjectDelete = !$object->allowRogue();
		}
		if (!empty($_POST['DeleteForm'])) {
			$this->response->task = 'status';
			$targetDescriptor = $this->params['model']->targetDescriptor;
			$this->params['model']->attributes = $_POST['DeleteForm'];
			if (!$this->params['model']->delete()) {
				$this->response->error =  'Could not delete '. $this->params['model']->targetDescriptor;
			} else {
				$this->response->success = ucfirst($targetDescriptor). ' has been deleted!';
				if (!empty($_GET['redirect'])) {
					$this->response->redirect = $_GET['redirect'];
				} else {
					$this->response->trigger = [
						['refresh', '.model-'. $primaryModel::baseClassName()]
					];
				}
			}
		}
	}

	/**
	 *
	 */
	public function actionDeleteOld() {
		$this->params['model'] = new DeleteForm;

		if (!empty($_GET['relation_id']) AND !empty($_GET['object'])) {
			$relationship = Relation::get($_GET['relation_id']);
			if (empty($relationship)) {
				throw new HttpException(404, "Unknown relationship ". (empty($_GET['relation_id']) ? '' : $_GET['relation_id']));
			}
			if ($_GET['object'] === 'parent') {
				$object = Registry::getObject($relationship->parent_object_id, true);
				$relationshipWith = Registry::getObject($relationship->child_object_id, true);
			} elseif($_GET['object'] === 'child') {
				$object = Registry::getObject($relationship->child_object_id, true);
				$relationshipWith = Registry::getObject($relationship->parent_object_id, true);
			}
			if (empty($object) OR empty($relationshipWith)) {
				throw new HttpException(404, "Unknown object");
			}
			if ($object->asa('RAclBehavior') AND !$object->can('delete')) {
				throw new RAccessException("You do not have access to delete this object.");
			}
			$this->params['model']->relationship = $relationship;
			$this->params['model']->relationshipWith = $relationshipWith;
			$this->params['model']->forceRelationshipDelete = false; // @todo if they can't delete object
			$this->params['model']->forceObjectDelete = $object->getGreenMile(array($relationshipWith->id));
			$response = new Response('delete', array('dialog' => true, 'dialogSettings' => array('title' => 'Delete '.$object->typeModule->title->getSingular(true) .' or Relationship', 'saveButton' => array('text' => 'Delete', 'class' => 'ui-state-error'), 'width' => '600px')));
		} else {
			if (empty($_GET['id']) or !($object = Registry::getObject($_GET['id'], true)) or !($type = $object->getTypeModule())) {
				throw new HttpException(404, "Unknown object ". (empty($_GET['id']) ? '' : $_GET['id']));
			}
			if ($object->asa('RAclBehavior') AND !$object->can('delete')) {
				throw new RAccessException("You do not have access to delete this object.");
			}
			$relationship = null;
			$response = new Response('delete', array('dialog' => true, 'dialogSettings' => array('title' => 'Delete '.$object->typeModule->title->getSingular(true),  'saveButton' => array('text' => 'Delete', 'class' => 'ui-state-error'),  'width' => '600px')));
		}

		$this->params['model']->object = $object;

		if (!empty($_POST['DeleteForm'])) {
			$this->params['model']->attributes = $_POST['DeleteForm'];
			if (!empty($_GET['redirect'])) {
				$response->redirect = $_GET['redirect'];
			} else {
				$response->refresh = '.ic-type-'. $object->typeModule->shortName;
			}

			if (!empty($_POST['target'])) {
				$this->params['model']->target = $_POST['target'];
			}

			if ($this->params['model']->delete()) {
				$response->success = ucfirst($this->params['model']->targetDescriptor). ' has been deleted!';;
			} else {
				
			}
		}

		$response->handle();
	}

	/**
	 *
	 */
	public function actionWatch() {
		$response = new Response(false);
		if (empty($_GET['id']) or !($object = Registry::getObject($_GET['id'])) or !($type = $object->getTypeModule())) {
			throw new HttpException(404, "Unknown object ". (empty($_GET['id']) ? '' : $_GET['id']));
		}
		$response->ajaxPackage['replace'] = CHtml::link('', array('unwatch', 'id' => $object->id), array('class' => 'ic-icon-darker-blue ic-icon-hover-gray ic-icon-24 ic-icon-eye ajax', 'title' => 'Stop Watching'));
		if ($object->watch(true)) {
			$response->success = $object->descriptor. ' is being watched!';;
		} else {
			$response->error =  'Could not watch '. $object->descriptor;
		}

		$response->handle();
	}


	/**
	 *
	 */
	public function actionUnwatch() {
		$response = new Response(false);
		if (empty($_GET['id']) or !($object = Registry::getObject($_GET['id'])) or !($type = $object->getTypeModule())) {
			throw new HttpException(404, "Unknown object ". (empty($_GET['id']) ? '' : $_GET['id']));
		}
		$response->ajaxPackage['replace'] = CHtml::link('', array('watch', 'id' => $object->id), array('class' => 'ic-icon-gray ic-icon-hover-blue ic-icon-24 ic-icon-eye ajax', 'title' => 'Start Watching'));
		if ($object->watch(false)) {
			$response->success = $object->descriptor. ' is no longer being watched!';;
		} else {
			$response->error =  'Could not unwatch '. $object->descriptor;
		}

		$response->handle();
	}


	/**
	 *
	 */
	public function actionWidget() {
		$package = array();
		$renderWidgets = array();
		if (!empty($_POST['widgets'])) {
			$renderWidgets = $_POST['widgets'];
			$baseState = array('fetch' => 0);
		} elseif (!empty($_GET['widgets'])) {
			$renderWidgets = $_GET['widgets'];
			$baseState = array('fetch' => 1);
			ob_start();
			ob_implicit_flush(false);
		}
		$sectionCount = count($renderWidgets);
		if (isset($_GET['sectionCount'])) {
			$sectionCount = (int) $_GET['sectionCount'];
		}
		if (isset($_POST['sectionCount'])) {
			$sectionCount = (int) $_POST['sectionCount'];
		}
		if (!empty($renderWidgets)) {
			foreach ($renderWidgets as $i => $widget) {
				$w = array();
				if (empty($widget['state'])) { $widget['state'] = array(); }
				if (empty($widget['data'])) { $widget['data'] = array(); }
				if (!isset($widget['data']['sectionCount'])) {
					$widget['data']['sectionCount'] = $sectionCount;
				}
				$w['rendered'] = Yii::$app->widgetEngine->build($widget['name'], $widget['data'], array(), array_merge($baseState, $widget['state']));
				$w['id'] =  Yii::$app->widgetEngine->lastBuildId;
				$package[$i] = $w;
			}
		}
		//sleep(3);
		$this->params['widgets'] = $package;
		//var_dump($package);exit;
		$this->json();
	}
}
