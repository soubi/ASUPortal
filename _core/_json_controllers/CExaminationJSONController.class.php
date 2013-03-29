<?php
/**
 * Created by JetBrains PhpStorm.
 * User: aleksandr
 * Date: 11.11.12
 * Time: 22:48
 * To change this template use File | Settings | File Templates.
 */
/*
 * Создание класса-наследника, содержащего информацию 
 * об экзаменационных вопросах
 */
class CExaminationJSONController extends CBaseJSONController {
	/**
	 * Получение курсов, для которых есть экзаменационные вопросы
	 * по указанной специальности
	 */
    public function actionGetCources() {
    //Получение введенного названия специальности
        $speciality = $_GET['speciality'];
        $res = array();
        //Поиск совпадения имеющихся названий с введенным
        foreach (CExamManager::getAllQuestions()->getItems() as $q) {
            if ($q->speciality_id == $speciality) {
            //При найденном совпадение запись курса
                $res[$q->course] = $q->course;
            }
        }
        //Вывод итогового массива
        echo json_encode($res);
    }
    /**
     * Получение учебных годов, для которых есть экзаменционные вопросы
     * по указанной специальности указанного курса. 
     * 
     * Зачем надо еще и год. Вопросы для 3-го курса в 2011-2012 и в 2010-2011 
     * учебных годах могут различаться, поэтому надо так. 
     */
    public function actionGetYears() {
    	$speciality = $_GET['speciality'];
    	$course = $_GET['course'];
    	$res = array();
    	foreach (CExamManager::getAllQuestions()->getItems() as $q) {
    		if ($q->speciality_id == $speciality) {
    			if ($q->course == $course) {
    				$res[$q->year_id] = $q->year->getValue();
    			}
    		}
    	}
    	echo json_encode($res);
    }

    /**
     * Получение дисциплин, для которых есть экзаменационные вопросы
     * в указанном году, для указанной дисциплины и специальности
     */
    public function actionGetDisciplines() {
        $speciality = $_GET['speciality'];
        $course = $_GET['course'];
        $year = $_GET['year'];
        $res = array();
        foreach (CExamManager::getAllQuestions()->getItems() as $q) {
            if ($q->speciality_id == $speciality) {
                if ($q->course == $course) {
                    if ($q->year_id == $year) {
                        $res[$q->discipline_id] = $q->discipline->getValue();
                    }
                }
            }
        }
        echo json_encode($res);
    }

    /**
     * Получение категорий вопросов для указанной дисциплины
     */
    public function actionGetCategories() {
        $speciality = $_GET['speciality'];
        $course = $_GET['course'];
        $year = $_GET['year'];
        $discipline = $_GET['discipline'];
        $res = array();
        foreach (CExamManager::getAllQuestions()->getItems() as $q) {
            if ($q->speciality_id == $speciality) {
                if ($q->course == $course) {
                    if ($q->year_id == $year) {
                        if ($q->discipline_id == $discipline) {
                            $res[$q->category_id] = $q->category->getValue();
                        }
                    }
                }
            }
        }
        echo json_encode($res);
    }
}
