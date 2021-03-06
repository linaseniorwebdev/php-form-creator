<?php

include_once 'PhpValidation.php';
include_once 'JavascriptValidation.php';

/**
 * Amac kolay ve hizli bir sekilde form olusturulabimesini saglamak.
 * @author Ferid Movsumov
 */
class Form {

    private $_action;
    private $_method;
    private $_additionalParams;
    private $_formElementsArray;
    private $_tableAttributes = "";
    private $_javascriptValidation = "";
    

    /**
     * Bir formda action url kesinlikle belirtilmelidir.
     * @param type $actionUrl required.
     * @param type $method optional
     * @param type $name optional
     */
    public function __construct($actionUrl, $method = "POST") {
        $this->setAction($actionUrl);
        $this->setMethod($method);
        $this->_phpValidation = new PhpValidation();
        $this->_javascriptValidation = new JavascriptValidation();
    }

    /**
     * Action döndürür
     * @return type 
     */
    public function getAction() {
        return $this->_action;
    }

    /**
     * İlgili dosyanın var olup olmadığını kontrol eder. 
     * file_get_contents ile dosyanın varlığını kontrol etmem ne kadar doğru emin değilim
     * Amaç geliştiricinin yazım hatsaından kaynaklanan hatanın kaynağını bulmasını kolaylaştırmak.
     * Eğer varsa set eder.
     * @param type $action
     * @throws Exception 
     */
    private function setAction($action) {
        if (file_get_contents($action)) {
            $this->_action = $action;
        } else {
            throw new Exception(__DIR__ . "/" . $action . " isimli dosya mevcut değil");
        }
    }

    /**
     * Metod dondürür post veya get
     * @return type 
     */
    public function getMethod() {
        return $this->_method;
    }

    /**
     * Method ya POST ya da GET olabilir
     * gelen stringe toupper fonksiyonu uygulanıyor.
     * @param type $method
     * @throws Exception 
     */
    public function setMethod($method) {
        $method = strtoupper($method);

        if ($method == "POST" || $method == "GET") {
            $this->_method = $method;
        } else {
            throw new Exception("Form method $method olamaz get veya post kullanmayı deneyin!");
        }
    }

    /**
     * Oluuşturduğumuz formu geri döndürür.
     * Ekrana basılacak tüm kodları bu metod döndürür 
     */
    public function show() {
        $result = "";
        
        $result.="\n" . $this->_javascriptValidation->getJavascriptValidationCode()."\n";

        $formAttributes = $this->getSettedAttributes();

        if (isset($this->_additionalParams)) {
            $formAttributes.=$this->_additionalParams;
        }
        
        $result.="<table $this->_tableAttributes >\n";

        $result.="<tr class='row'><td id='warnings' colspan='2' class='warnings'></td></tr>";

        $result.="<form $formAttributes>\n";

        if (isset($this->_formElementsArray)) {
            foreach ($this->_formElementsArray as $formElement) {
                $result.="<tr class='row'>" . $formElement . "</tr>\n";
            }
        }
        return $result;
    }

    /**
     * Set edilmiş form attributlarını döndürür birleşik bir string şeklinde
     * @return type 
     */
    public function getSettedAttributes() {
        $result = " ";

        if (isset($this->_action)) {
            $result.="action='$this->_action' ";
        }

        if (isset($this->_method)) {
            $result.="method='$this->_method' ";
        }
        return $result;
    }

    /**
     * Ek parametreler için kullanılır
     * Çok sık ihtiyaç duyulmayan parametreler,
     * array olarak key value şeklinde buraya gönderilecek
     * @param type $params
     * @return \strıng 
     */
    public function setFormAttributes($params) {
        $result = " ";
        foreach ($params as $key => $value) {
            $result.=$key . "='" . $value . "' ";
        }

        $this->_additionalParams = $result;
    }

    /**
     * $label: <input type='$type' name='$name' /> şeklinde bir form elemanı oluşturmak için kullanılır.
     * @param type $type input için type parametresi belirtmek zorunludur. 
     * password, text veya submit olabilir
     * @param type $name
     * Form elemanına atayacağınız isimdir.
     * @param type $label
     * Form elemanının labelidir.
     * @param type $additionalParams
     * eklemek istediğiniz ek parametreleri key value çiftleri şeklinde
     * parametre olarak vermeniz gerekiyor. Ek attribute vermek istemiyorsanız boş bırakınız
     * @throws Exception 
     */
    public function addInput($type, $name, $label = "", $additionalParams = array()) {

        $originalLabel = $label;
        $result = "";
        $colspan = "";

        if ($label != "" && $type != "submit") {
            $label.=": ";
            $label = "<td class='col one'>" . $label . "</td>";
            $class = 'col two';
        } else { //Button ise
            $additionalParams["value"] = $label;
            $label = "";
            $class = 'col double';
            $colspan = "colspan='2'";
        }

        $result.="$label <td class='$class' $colspan ><input ";

        if (isset($type)) {
            $result.="type='" . $type . "' ";
        } else {
            throw new Exception("Input için type parametresi tanımlamalısınız!");
        }

        if (isset($name)) {
            $result.="name='" . $name . "' ";
            $result.="id='" . $name . "' ";
        } else {
            throw new Exception("Input için name parametresi tanımlamalısınız!");
        }

        foreach ($additionalParams as $key => $value) {
            $result.=$key . "='" . $value . "' ";
        }

        $result.=" /></td><br />\n";

        $this->_formElementsArray[$name . "-" . $originalLabel . "-" . $type] = $result;

        return $this;
    }

    /**
     * textarea oluşturmak için kullanılır. 
     * cols ve rows değerlerini tırnaksız bir şekilde direk integer...
     * olarak vermeye dikkat ediniz.
     * @param type integer
     * @param type integer
     * @param type $label
     * @param type $optionalAttributes 
     */
    public function addTextArea($cols, $rows, $name, $label, $optionalAttributes = array()) {
        $result = "";
        $attributes = "";
        $originalLabel = $label;

        if (is_integer($cols)) {
            $attributes.="cols='" . $cols . "' ";
        } else {
            $attributes.="cols='25' ";
        }

        if (is_integer($rows)) {
            $attributes.="rows='" . $rows . "' ";
        } else {
            $attributes.="rows='5' ";
        }

        $optionalAttributes['name'] = $name;
        $optionalAttributes['id'] = $name;

        foreach ($optionalAttributes as $key => $value) {
            $attributes.=$key . "='" . $value . "'";
        }

        $result.="<td colspan='2' class='col double'>$label:<br/><textarea $attributes >";
        if (isset($optionalAttributes['content'])) {
            $result.=$optionalAttributes['content'];
        }
        $result.="</textarea></td>";

        $this->_formElementsArray[$name . "-" . $originalLabel . "-" . "textarea"] = $result;
        return $this;
    }

    /**
     * Tabloya ek attributlar atamak için kullanılır.
     * @param type $attributes 
     */
    public function setTableAttributes($attributes = array()) {
        $result = "";
        foreach ($attributes as $key => $value) {
            $result.="$key='" . $value . "' ";
        }

        $this->_tableAttributes = $result;
    }

    /**
     *
     * @param type $name
     * @param type $value
     * @param type $label
     * @param type $additionalParams 
     */
    public function addCheckBox($name, $value, $label, $additionalParams = array()) {

        $originalLabel = $label;

        //Kullanıcıdan alınan parametreleri ekliyoruz
        if (isset($name) && !empty($name) && isset($value) && !empty($value))
            $attributes = array(
                'type' => 'checkbox',
                'name' => $name,
                'value' => $value,
                'id' => $name
            );

        //ek parametreleri ekliyoruz
        foreach ($additionalParams as $attributeName => $attributeValue) {
            $attributes[$attributeName] = $attributeValue;
        }

        $attributesString = "";
        //Butun parametreleri tek bir string haline dönüştüreceğiz.
        foreach ($attributes as $attributeKey => $val) {
            $attributesString.=$attributeKey . "='" . $val . "' ";
        }

        $result = "<td class='col double' colspan='2' ><div id='$name'><input $attributesString /> $label </div></td>";

        $this->_formElementsArray[$name . "-" . $originalLabel . "-" . "checkbox"] = $result;
        return $this;
    }

    /**
     * Radio buton eklemek için kullanılır. 
     * @param type $name
     * @param type $value
     * @param type $label Radio buttonun sağındaki text
     * @param type $additionalParams ek parametreler
     */
    public function addRadioButton($name, $value, $label, $additionalParams = array()) {

        $originalLabel = $label;

        //Kullanıcıdan alınan parametreleri ekliyoruz
        if (isset($name) && !empty($name) && isset($value) && !empty($value))
            $attributes = array(
                'type' => 'radio',
                'name' => $name,
                'value' => $value,
                'id' => $value,
            );

        //ek parametreleri ekliyoruz
        foreach ($additionalParams as $attributeName => $attributeValue) {
            $attributes[$attributeName] = $attributeValue;
        }

        $attributesString = "";
        //Butun parametreleri tek bir string haline dönüştüreceğiz.
        foreach ($attributes as $attributeKey => $val) {
            $attributesString.=$attributeKey . "='" . $val . "' ";
        }

        $result = "<td class='col double' colspan='2' ><input $attributesString /> $label <br/></td>";

        $this->_formElementsArray[$name . "-" . $originalLabel . "-radio"] = $result;
        return $this;
    }

    /**
     * Bir satır text eklemek için kullanılabilir
     * @param type $text 
     */
    public function addLabel($text) {
        $result = "<td class='col double' colspan='2' >$text<br/></td>\n";

        $this->_formElementsArray[] = $result;
    }

    public function addComboBox($name, $label = "", $optionsArray = array()) {
        if (!empty($label)) {
            $label.=":";
        }
        $options = "";
        foreach ($optionsArray as $optionKey => $optionValue) {
            $options.="<option value='$optionKey' >$optionValue</option>\n";
        }

        $result = "<td class='col one' >$label</td><td class='col two'>\n<select id='$name' name='$name'>\n$options</select>\n<br/></td>";

        $this->_formElementsArray[$name . "-" . $label] = $result;

        return $this;
    }

    /**
     * Validation kuralları atanır required, min, max,mustBeChecked şu an için desteklenen javascript
     * calidation türleridir.
     * @param type $validationRulesArray
     * @throws Exception 
     */
    public function setValidation($validationRulesArray = array()) {
        $lastHtmlElementIdAndLabel = "";
        $lastHtmlElementIdAndLabel = array_pop(array_keys($this->_formElementsArray));

        $index = explode('-', $lastHtmlElementIdAndLabel);

        $elementLabel = "";
        $elementId = "";

        $elementLabel = $index[1];
        $elementId = $index[0];
        $elementType = $index[2];

        if (isset($elementId) && isset($elementLabel) && isset($elementType)) {
            $this->_javascriptValidation->setFormElementId($elementId);
            $this->_javascriptValidation->setLabel($elementLabel);
            $this->_javascriptValidation->setType($elementType);
        } else {
            throw new Exception("Id, Type veya Label atanmamış");
        }
        //input type text veya textarea oldugu durumlar icin
        //@Todo kontroller biraz daha sıkı yapılacak
        if (isset($validationRulesArray['required'])) {
            $this->_javascriptValidation->setRequired($validationRulesArray['required']);
        }

        if (isset($validationRulesArray['min'])) {
            $this->_javascriptValidation->setMinLength($validationRulesArray['min']);
        }

        if (isset($validationRulesArray['max'])) {
            $this->_javascriptValidation->setMaxLength($validationRulesArray['max']);
        }

        if (isset($validationRulesArray['mustBeChecked'])) {
            $this->_javascriptValidation->setMustBeChecked($validationRulesArray['mustBeChecked']);
        }
        $this->_javascriptValidation->generateCode();
    }

    /**
     * Kullanıcı sözleşmesi eklenmesini kolaylaştırı
     * textarea içerisinde kullanıcı sözleşmesi ve bir CheckBox atanır
     * @param type $filePath Kullanıcı sözleşmesi için bir dosya yolu vermeniz gerekir
     * @param type $readControl Javascript kontrollerinin yapılıp yapılmayacağını belirler yapılması için true olmalıdır
     */
    public function addTermsOfUse($filePath, $readControl = false) {
        
        if (file_exists($filePath)) {
            $this->addTextArea(35, 10, "touContent", "Kullanıcı Sözleşmesi",array("style"=>"resize:none","readonly"=>"readonly","content" => file_get_contents($filePath)));
        }
           
        $this->addCheckBox("kullanicisozlesmesi", "kullanicisozlesmesi", "Kullanıcı sözleşmesini okudum",array("id"=>"touCheckBox"));
        
        if($readControl)
        {
            $this->_javascriptValidation->addTermsOfUseReadControl();
        }
    }
}
?>