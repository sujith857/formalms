import 'regenerator-runtime/runtime'
const axios = require('axios');
import Tree from '../twig/tree.html.twig';

class FolderTree {

  constructor(baseApiUrl, controller, type) {
    const _this = this;

    _this.baseApiUrl = baseApiUrl;
    _this.controller = controller;
    _this.type = type;
    _this.container = document.querySelector('*[data-container=' + _this.type + ']');

    _this.container.addEventListener('createTreeItem', (e) => {
      _this.refresh(e.detail.selectedId);
    });
    _this.container.addEventListener('deleteTreeItem', () => {
      _this.refresh();
    });
    _this.container.addEventListener('reorderData', () => {
      _this.refresh();
    });
    _this.container.addEventListener('click', (event) => {
      this.clickOnFolder(event);
    });

    _this.initDragDrop();
    _this.getStatus();// From localStorage
    _this.getData(_this.getApiUrl('getfoldertree'));
  }

  clickOnFolder(event) {
    const _this = this;
    event.preventDefault();

    // Try to get clicked dir
    let el = event.target.closest('.folderTree__link');
    if (!el) {
      const li = event.target.closest('.folderTree__li');
      if (li) {
        el = li.querySelector('.folderTree__link');
      }
    }

    if (!el) {
      return;
    }

    const elId = el.getAttribute('data-id');

    // dispatch openDir event
    _this.container.dispatchEvent(new CustomEvent('openDir', {
      detail: { selectedId: elId, }
    }));

    const isOpen = el.classList.contains('ft-is-folderOpen');
    const clickOnArrow = event.target.classList.contains('arrow');

    // Remove all selections
    const els = _this.container.querySelectorAll('.folderTree__link');
    if (els) {
      els.forEach(el => {
        el.classList.remove('ft-is-selected');
      });
    }

    // Hide/Show children of clicked dir
    const childrenUl = el.parentNode.querySelector('.folderTree__ul');

    if (isOpen) {
      if (clickOnArrow) {
        event.target.classList.remove('opened');
        el.classList.remove('ft-is-folderOpen');

        if (childrenUl) {
          childrenUl.classList.add('hidden');
        }
      } else {
        el.classList.add('ft-is-selected');
      }
    } else {
      if (clickOnArrow) {
        event.target.classList.add('opened');
        el.classList.add('ft-is-folderOpen');
      } else {
        const li = event.target.closest('.folderTree__li');
        const arrow = li.querySelector('.arrow');
        el.classList.add('ft-is-selected');
        if (arrow) {
          arrow.classList.add('opened');
          el.classList.add('ft-is-folderOpen');
        }
      }
      if (childrenUl) {
        childrenUl.classList.remove('hidden');
      }
    }

    _this.setOpenedDirs();
    _this.setSelectedDir();
  }

  initDragDrop() {
    this.container.addEventListener('dragstart', this.onDragStart.bind(this));
    this.container.addEventListener('dragover', this.onDragOver.bind(this));
    this.container.addEventListener('dragleave', this.onDragLeave.bind(this));
    this.container.addEventListener('drop', this.onDrop.bind(this));
  }

  selectItems() {
    this.container.querySelectorAll('.folderView__li').forEach((item) => {
      item.classList.remove('fv-is-selected');
      const item_id = parseInt(item.getAttribute('data-id'));

      if (this.currentElsIds.includes(item_id)) {
        item.classList.add('fv-is-selected');
      }
    });
  }

  onDragStart(event) {
    if (event.target.classList.contains('is-droppable')) {
      this.currentEl = event.target;
      this.currentElId = parseInt(this.currentEl.getAttribute('data-id'));

      this.currentEls = this.container.querySelectorAll('.fv-is-selected');
      this.currentElsIds = [];
      this.currentEls.forEach((item) => {
        this.currentElsIds.push(parseInt(item.getAttribute('data-id')));
      });

      // Single drop
      if (!this.currentElsIds.includes(this.currentElId)) {
        this.currentEls = [event.target];
        this.currentElsIds = [this.currentElId];
        this.selectItems();
      }
    }
  }

  onDragOver(event) {
    const target = event.target;

    if (this.currentElsIds) {
      if (!this.currentElsIds.includes(target.getAttribute('data-id')) && target.classList.contains('is-dropzone')) {
        target.classList.add('fv-is-dropped');
        event.preventDefault();
      }
    }
  }

  onDragLeave(event) {
    const target = event.target;

    if (this.currentElsIds) {
      if (!this.currentElsIds.includes(target.getAttribute('data-id')) && target.classList.contains('is-dropzone')) {
        target.classList.remove('fv-is-dropped');
      }
    }
  }

  setOpenedDirs() {
    const openedEls = this.container.querySelectorAll('.ft-is-folderOpen');
    this.openedIds = [];

    openedEls.forEach((item) => {
      let id = item.getAttribute('data-id');
      if (id > 0) {
        this.openedIds.push(id);
      }
    });
    this.storeStatus();
  }

  setSelectedDir() {
    const item = this.container.querySelector('.ft-is-selected');
    if (item) {
      this.selectedId = item.getAttribute('data-id');
    }
    this.storeStatus();
  }

  storeStatus() {
    localStorage.setItem('openedIds', this.openedIds);
    localStorage.setItem('selectedId', this.selectedId);
  }

  getStatus() {
    const _this = this;
    _this.openedIds = localStorage.getItem('openedIds') ? localStorage.getItem('openedIds').split(',') : [];
    _this.selectedId = localStorage.getItem('selectedId');
  }

  refresh(parentId) {
    const _this = this;

    if (parentId >= 0) {
      _this.selectedId = parentId;
    }

    _this.getData(_this.getApiUrl('getfoldertree'));
  }

  onDrop(event) {
    const target = event.target;
    target.classList.remove('fv-is-dropped');

    if (this.currentElsIds) {
      const parentId = parseInt(target.getAttribute('data-id'));

      if (!this.currentElsIds.includes(parentId) && target.classList.contains('is-dropzone')) {
        this.reorderData(this.getApiUrl('move', { ids: this.currentElsIds, newParent: parentId }));
      }
    }
  }

  async reorderData(endpoint) {
    const _this = this;

    try {
      await axios.get(endpoint).then(() => {
        _this.refresh();
      }).catch( (error) => {
        console.log(error);
      });
    } catch (e) {
      console.log(e);
    }
  }

  async getData(endpoint) {
    const _this = this;
    try {
      await axios.get(endpoint).then((response) => {
        const tree = Tree({ data: response.data.data[0].children });

        const treeView = _this.container.querySelector('.folderTree__ul .folderTree__ul');
        treeView.innerHTML = tree;

        if (_this.openedIds) {
          _this.openedIds.forEach((id) => {
            if (id != _this.selectedId) {
              let arrow = _this.container.querySelector('.folderTree__li[data-id="' + id + '"] .arrow');
              if (arrow) {
                arrow.click();
              }
            }
          });
        }
        if (_this.selectedId > 0) {
          let dir = _this.container.querySelector('.folderTree__link[data-id="' + _this.selectedId + '"]');
          if (dir) {
            dir.classList.add('ft-is-selected');
            dir.click();
          }
        }
        if (!_this.container.querySelector('.js-disable-context-menu')) {
          // dispatch refreshContextMenu event
          document.dispatchEvent(new CustomEvent('refreshContextMenu', { detail: { controller: _this.controller }}));
        }
      }).catch((error) => {
        console.log(error)
      });
    } catch (e) {
      console.log(e);
    }
  }

  getApiUrl(action, params) {
    let url = `${this.baseApiUrl}/${action}`;
    if (!params) {
      params = {};
    }
    url += '&' + new URLSearchParams(params).toString();

    return url;
  }
}

export default FolderTree
