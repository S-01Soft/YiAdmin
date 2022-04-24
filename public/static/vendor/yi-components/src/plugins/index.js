import Select from './Select';
import ColorPicker from './ColorPicker';
import DatePicker from './DatePicker';
import EditInput from './EditInput';
import Exports from './Exports';
import Icon from './Icon';
import Attachment from './Attachment';
import Image from './Image';
import Import from './Import';
import Kv from './Kv';
import KvStr from './KvStr';
import List from './List';
import Option from './Option';
import Search from './Search';
import Upload from './Upload';
import Editor from './Editor';
import Markdown from './Markdown';
import Textarea from './Textarea';
import RangePicker from './RangePicker';
import Switch from './Switch';
import Checkbox from './Checkbox';
import Inputs from './Inputs';
import Menu from './Menu';
import SubMenu from './SubMenu';

const components = [
    Select,
    ColorPicker,
    DatePicker,
    EditInput,
    Exports,
    Icon,
    Image,
    Attachment,
    Import,
    Kv,
    KvStr,
    List,
    Option,
    Search,
    Upload,
    Editor,
    Markdown,
    Textarea,
    RangePicker,
    Switch,
    Checkbox,
    Inputs,
    Menu,
    SubMenu
];

const install = function(Vue) {
    components.forEach(component => {
        Vue.component(component.name, component);
    });
}

export {
    install, 
    Select,
    ColorPicker,
    DatePicker,
    EditInput,
    Exports,
    Icon,
    Attachment,
    Image,
    Import,
    Kv,
    KvStr,
    List,
    Option,
    Search,
    Upload,
    Editor,
    Markdown,
    Textarea,
    RangePicker,
    Switch,
    Checkbox,
    Inputs,
    Menu,
    SubMenu
}

