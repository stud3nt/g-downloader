import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {ParserRequest} from "../../../model/parser-request";
import {NodeStatus} from "../../../enum/node-status";
import {Pagination} from "../../../model/pagination";
import {PaginationMode} from "../../../enum/pagination-mode";
import {ParserNode} from "../../../model/parser-node";
import {Tag} from "../../../model/tag";
import {ParserToolbarAction} from "../../../enum/parser-toolbar-action";

@Component({
	selector: 'app-parser-toolbar',
	templateUrl: './parser-toolbar.component.html',
	styleUrls: ['./parser-toolbar.component.scss']
})
export class ParserToolbarComponent implements OnInit {

	public NodeStatus = NodeStatus;
	public PaginationMode = PaginationMode;
	public ToolbarAction = ParserToolbarAction;

	public _parserRequest: ParserRequest = null;
	public _currentNode: ParserNode = null;
	public _pagination: Pagination = null;

	public _nodeCategory: string = null;
	public _inputTagName: string = null;
	public _tagInputVisible: boolean = false;

	public _toolbarAction: string = null;

	public _assesments = [];

	// tag search timeout variable;
	private _tagSearchTimeout = null;
	// tag search - found tags;
	public _foundTags: Tag[] = [];

	public _displayMode: string = 'standard';
	public _containerHeight: number = 0;

	// pages packages list
	public _packages: any[] = [];
	// currently active package
	public _activePackage: any = null;

	// active selector
	public _activeSelectorValue: string = null;
	// active selector children
	public _activeSelectorChildrenValue: string = null;

	// pages choices list
	public _pages: any[] = [];
	// currently selected page
	public _currentPage: any = null;

	public _nodeTitle: string = '&nbsp;';
	public _toolbarContainerClasses: string = 'parser_toolbar';
	public _toolbarMaskClasses: string = 'parser_toolbar_mask';

	private _alphabet = Array.from(Array(26), (e, i) => String.fromCharCode(i + 97));

	constructor() { }

	@Input() set parserRequest(parserRequest: ParserRequest) {
		let countString = '';

		if (parserRequest.files && parserRequest.parsedNodes.length === 0)
			countString = ' ('+parserRequest.files.length+' files)';
		else if (parserRequest.parsedNodes)
			countString = ' ('+parserRequest.parsedNodes.length+' subnodes)';

		this._nodeTitle = ('&nbsp;' + ((parserRequest.currentNode && parserRequest.currentNode.name) ? (parserRequest.currentNode.name + countString) : ''));
		this._parserRequest = parserRequest;
		this._currentNode = parserRequest.currentNode;
		this._pagination = parserRequest.pagination;
		this._nodeCategory = (this._currentNode.category) ? this._currentNode.category.symbol : null;

		this.createPaginationData();
	}

	@Input() set displayMode(mode: string) {
		this._displayMode = mode;
		this._toolbarContainerClasses = 'parser_toolbar';
		this._toolbarMaskClasses = 'parser_toolbar_mask';

		if (mode === 'fixed') {
			this._toolbarContainerClasses += ' fixed';
			this._toolbarMaskClasses += ' visible';
			this._containerHeight = document.getElementById('parser-toolbar-container').offsetHeight + 10;
		} else {
			this._containerHeight = 0;
		}
	}

	@Output() onNodeUpdate = new EventEmitter<ParserNode>();
	@Output() onNodePaginating = new EventEmitter<Pagination>();

	ngOnInit() {}

	public nodeMarking(selectedStatus: string): void {
		this._parserRequest.currentNode.toggleStatus(selectedStatus);
		this.updateCurrentNode();
	}

	public nodePaginating(reset: boolean = false): void {
		this._pagination.currentPackage = (this._activePackage) ? this._activePackage.id : null;
		this._pagination.reset = reset;

		switch (this._pagination.mode) {
			case PaginationMode.Letters:
				this._pagination.currentLetter = this._currentPage;
				break;

			case PaginationMode.Numbers:
				this._pagination.currentPage = this._currentPage;
				break;
		}

		this.onNodePaginating.next(this._pagination);
	}

	public changePage(value: number = 1): void {
		if (this._pages) {
			for (let pageKey in this._pages) {
				let page = this._pages[pageKey];

				if (page === this._currentPage && typeof this._pages[(pageKey + value)] !== 'undefined') {
					this._currentPage = this._pages[(pageKey + value)];
					break;
				}
			}

			this.nodePaginating();
		}
	}

	public setCategory(): void {
		this._parserRequest.currentNode.category = null;

		let categories = this._parserRequest.categories;

		if (categories) {
			for (let category of categories) {
				if (category.symbol === this._nodeCategory) {
					this._parserRequest.currentNode.category = category;
					break;
				}
			}
		}

		this.updateCurrentNode();
	}

	public openTagInput(): void {
		this._tagInputVisible = true;

		setTimeout(() => {
			document.getElementById('toolbar-tag-input').focus();
		}, 10);
	}

	public toggleToolbarAction(action: string = null): void {
		this._toolbarAction = ((this._toolbarAction === null) ? action : null);
	}

	public toggleTag(event: KeyboardEvent): void {
		clearTimeout(this._tagSearchTimeout);

		this._foundTags = [];

		let tagName = (this._inputTagName) ? this._inputTagName.toUpperCase() : '';

		if (event.key === 'Enter' && this._inputTagName.length > 0) { // tag creation
			let tag = this._parserRequest.findTagByName(tagName);

			if (!tag) {
				tag = new Tag();
				tag.name = tagName;

				this._parserRequest.tags.push(tag);
			}

			this._parserRequest.currentNode.addTag(tag);
			this._inputTagName = null;
			this._tagInputVisible = false;
			this._foundTags = [];

			this.updateCurrentNode();
		} else {
			this._tagSearchTimeout = setTimeout(() => {
				for (let tagIndex in this._parserRequest.tags) {
					if (this._inputTagName.length < 3 || this._foundTags.length >= 10)
						break;

					let processedTag = this._parserRequest.tags[tagIndex];

					if (processedTag.name.indexOf(tagName) !== -1)
						this._foundTags.push(processedTag);
				}
			}, 300);
		}
	}

	public addTag(tag: Tag): void {
		this._parserRequest.currentNode.addTag(tag);
		this._tagInputVisible = false;
		this._foundTags = [];
		this.updateCurrentNode();
	}

	public removeTag(tag: Tag): void {
		this._parserRequest.currentNode.removeTag(tag);
		this._inputTagName = null;
		this.updateCurrentNode();
	}

	public clearRatingForm(): void {
		this._parserRequest.currentNode.description = this._currentNode.description;
		this._parserRequest.currentNode.rating = this._currentNode.rating;
		this._toolbarAction = null;
	}

	public updateCurrentNode(): void {
		this.onNodeUpdate.next(this._parserRequest.currentNode);
	}

	public setActiveSelector(selector: string): void {
		this._activeSelectorValue = selector;
		this._pagination.setActiveSelectorByValue(selector);

		if (this._pagination.getActiveSelector().childrens.length === 0)
			this.nodePaginating(true);
	}

	public setActiveChildren(children: string): void {
		if (this._activeSelectorValue) {
			this._pagination.getActiveSelector().setActiveChildrenByValue(children);
			this._pagination.setActiveSelectorByValue(this._activeSelectorValue);
			this.nodePaginating(true);
		}
	}

	public nodeStatusButtonClass(checkedStatus: string): string {
		let buttonClasses = 'button button-small';

		if (this._parserRequest.currentNode && this._parserRequest.currentNode.hasStatus(checkedStatus))
			buttonClasses += ' active';

		return buttonClasses;
	}

	private createPaginationData(): void {
		this._pages = [];
		this._packages = [];
		this._assesments = [];

		switch (this._pagination.mode) {
			case PaginationMode.Letters:
				for (let index in this._alphabet) {
					this._pages.push(this._alphabet[index].toUpperCase());
				}

				this._pages.push('Symbol');
				this._currentPage = this._pagination.currentLetter.toUpperCase();
				break;

			case PaginationMode.Numbers:
				for (let x = 1; x <= this._pagination.totalPages; x++)
					this._pages.push(x);

				this._currentPage = (this._pagination.currentPage + this._pagination.pageShift);
				break;

			case PaginationMode.LoadMore:
				if (this._pagination.packageSize > 0)
					for (let packageId = this._pagination.minPackage; packageId <= this._pagination.maxPackage; packageId++) {
						let pck: any = {};

						pck.id = packageId;
						pck.size = (packageId * this._pagination.packageSize);

						this._packages.push(pck);

						if (!this._activePackage)
							this._activePackage = pck;
					}
				break;
		}

		if (this._pagination.selectors)
			if (this._pagination.getActiveSelector()) {
				let selector = this._pagination.getActiveSelector();
				this._activeSelectorValue = selector.value;

				if (selector.getActiveChildren()) {
					let child = selector.getActiveChildren();
					this._activeSelectorChildrenValue = child.value;
				}
			}

		for (let x = 1; x <= 10; x++)
			this._assesments.push(x);
	}
}
