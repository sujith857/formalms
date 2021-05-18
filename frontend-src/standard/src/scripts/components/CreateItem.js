import Config from '../config/config';
const axios = require('axios');

class CreateItem {

  constructor(type) {
    this.type = type;
    this.container = document.querySelector('*[data-container=' + this.type + ']');

    this.initDropdown();
  }

  getApiUrl(action, id, params) {
    let url = `${Config.apiUrl}lms/lo/${action}`;
    if (id) {
      url += `&id=${id}`;
    }
    if (!params) {
      params = {};
    }
    params.type = this.type;
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }

  initDropdown() {
    const dropdown = this.container.querySelector('#dropdownMenu_' + this.type);
    const types = dropdown.querySelectorAll('.itemType');
    const treeLinks = this.container.querySelectorAll('.folderTree__li');

    if (types) {
      types.forEach(type => {
        type.addEventListener('click', (e) => { this.clickOnType(e) });
      });
    }
    if (treeLinks) {
      treeLinks.forEach(l => {
        l.addEventListener('click', (e) => { this.clickOnFolder(e) });
      });
    }
    const createFolderForm = this.container.querySelector('.createFolderForm');
    const createFolderBtn = createFolderForm.querySelector('.createFolder__btn');
    const cancelCreateFolderBtn = createFolderForm.querySelector('.cancelCreateFolder__btn');
    const folderInputText = createFolderForm.querySelector('.createFolder__input');

    createFolderBtn.addEventListener('click', (e) => { this.createNewFolder(e, createFolderForm) });
    cancelCreateFolderBtn.addEventListener('click', () => {
      const dropdownBtn = this.container.querySelector('#dropdownMenuBtn_' + this.type);
      dropdownBtn.classList.remove('hidden');
      dropdown.classList.remove('hidden');
      createFolderForm.classList.add('hidden');
      createFolderForm.querySelector('.createFolder__input').value = '';
    });

    folderInputText.addEventListener('keypress', (e) => {
      if (e.keyCode === 13) {
        this.createNewFolder(e, createFolderForm);
        return false;
      }
    });
  }

  clickOnFolder(event) {
    const el = event.target;
    const elId = el.getAttribute('data-id');
    this.selectedNodeId = elId;
    event.preventDefault();
  }
  
  createNewFolder(event, createFolderForm) {
    const _this = this;
    this.showErr();
    const text = createFolderForm.querySelector('.createFolder__input').value;
    const authentic_request = createFolderForm.querySelector('input[name=authentic_request]').value;
    const dropdownBtn = _this.container.querySelector('#dropdownMenuBtn_' + _this.type);
    const dropdown = _this.container.querySelector('#dropdownMenu_' + _this.type);
    const selectedNodeId = _this.selectedNodeId ? _this.selectedNodeId : 0;

    const params = {
      folderName: text,
      selectedNode: selectedNodeId,
      type: _this.type,
      authentic_request,
    }
    const apiUrl = _this.getApiUrl('createFolder', null, params);

    axios.get(apiUrl).then((response) => {
      if (response) {
        dropdownBtn.classList.remove('hidden');
        dropdown.classList.remove('hidden');
        createFolderForm.classList.add('hidden');
        createFolderForm.querySelector('.createFolder__input').value = '';

        // Refresh tree of parent node
        _this.refresh(selectedNodeId);
      }
    }).catch((error) => {
      _this.showErr(error.response.data.error);
    });

    event.preventDefault();
  }

  setOpenedDirs() {
    const _this = this;
    const openedEls = _this.container.querySelectorAll('.ft-is-folderOpen');
    this.openedIds = [];

    openedEls.forEach((item) => {
      let id = item.getAttribute('data-id');
      if (id > 0) {
        _this.openedIds.push(id);
      }
    });
  }

  setSelectedDir() {
    const _this = this;
    const item = _this.container.querySelector('.ft-is-selected');
    _this.selectedId = item ? item.getAttribute('data-id') : 0;
  }

  refresh(selectedId) {
    const _this = this;
    _this.setOpenedDirs();
    if (selectedId) {
      _this.selectedId = selectedId;
    } else {
      _this.setSelectedDir();
    }
    _this.container.querySelector('.folderTree__link.ft-is-root').click();
  }

  showErr(msg) {
    const _this = this;
    const err = _this.container.querySelector('.createFolder__input_err');
    if (msg) {
      err.innerHTML = msg;
      err.classList.remove('hidden');
    } else {
      err.classList.add('hidden');
    }
  }

  getNewLoUrl() {
    const _this = this;
    const selectedNodeId = _this.selectedNodeId ? _this.selectedNodeId : 0;

    return `index.php?modname=storage&op=display&${_this.type}_createLOSel=1&radiolo=${_this}&treeview_selected_${_this.type}=${selectedNodeId}`;
  }

  clickOnType(event) {
    const _this = this;
    const el = event.target;
    const dropdownBtn = _this.container.querySelector('#dropdownMenuBtn_' + _this.type);
    const dropdown = _this.container.querySelector('#dropdownMenu_' + _this.type);
    const createFolderForm = _this.container.querySelector('.createFolderForm');

    if (el) {
      const type = el.getAttribute('data-id');
      if (type === 'folder') {
        dropdownBtn.classList.add('hidden');
        dropdown.classList.add('hidden');
        createFolderForm.classList.remove('hidden');
      } else {
        location.href = _this.getNewLoUrl();
      }
      event.preventDefault();
    }
  }

}

export default CreateItem
