import {Component, EventEmitter, Input, Output, OnInit} from '@angular/core';
import {ParserRequest} from "../../../model/parser-request";
import {NodeStatus} from "../../../enum/node-status";
import {Pagination} from "../../../model/pagination";
import {PaginationMode} from "../../../enum/pagination-mode";

@Component({
	selector: 'app-parser-toolbar',
	templateUrl: './parser-toolbar.component.html',
	styleUrls: ['./parser-toolbar.component.scss']
})
export class ParserToolbarComponent implements OnInit {

	public NodeStatus = NodeStatus;
	public PaginationMode = PaginationMode;

	public _parserRequest: ParserRequest = null;
	public _pagination: Pagination = null;

	public _displayMode: string = 'standard';

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

	// node title
	public _nodeTitle: string = '&nbsp;';
	public _toolbarContainerClasses: string = 'parser_toolbar';
	public _toolbarMaskClasses: string = 'parser_toolbar_mask';

	private _alphabet = Array.from(Array(26), (e, i) => String.fromCharCode(i + 97));

	constructor() { }

	@Input() set parserRequest(parserRequest: ParserRequest) {
		let countString = '';

		if (parserRequest.files)
			countString = ' ('+parserRequest.files.length+' files)';
		else if (parserRequest.parsedNodes)
			countString = ' ('+parserRequest.parsedNodes.length+' subnodes)';

		this._nodeTitle = ('&nbsp;' + ((parserRequest.currentNode && parserRequest.currentNode.name) ? (parserRequest.currentNode.name + countString) : ''));
		this._parserRequest = parserRequest;
		this._pagination = parserRequest.pagination;
		this.createPaginationData();
	}

	@Input() set displayMode(mode: string) {
		this._displayMode = mode;
		this._toolbarContainerClasses = 'parser_toolbar';
		this._toolbarMaskClasses = 'parser_toolbar_mask';

		if (mode === 'fixed') {
			this._toolbarContainerClasses += ' fixed';
			this._toolbarMaskClasses += ' visible';
		}
	}

	@Output() onCurrentNodeMarking = new EventEmitter<string>();
	@Output() onNodePaginating = new EventEmitter<Pagination>();

	ngOnInit() {}

	public nodeMarking(selectedStatus: string): void {
		this.onCurrentNodeMarking.next(selectedStatus);
	}

	public nodePaginating(reset: boolean = false): void {
		this._pagination.currentPackage = this._activePackage.id;
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

				if (page === this._currentPage && typeof this._pages[(pageKey + value)] !== 'undefined')
					this._currentPage = this._pages[(pageKey + value)];
			}

			this.nodePaginating();
		}
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

	public createPaginationData(): void {
		this._pages = [];
		this._packages = [];

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

				this._currentPage = this._pagination.currentPage;
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
	}

	public nodeStatusButtonClass(checkedStatus: string): string {
		let buttonClasses = 'button button-small';

		if (this._parserRequest.currentNode && this._parserRequest.currentNode.hasStatus(checkedStatus))
			buttonClasses += ' active';

		return buttonClasses;
	}

}
