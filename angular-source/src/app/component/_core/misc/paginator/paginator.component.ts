import {Component, EventEmitter, Input, Output, OnInit, OnChanges, SimpleChanges} from '@angular/core';
import { PaginationMode } from "../../../../enum/pagination-mode";
import { Pagination } from "../../../../model/pagination";
import {PaginationSelector} from "../../../../model/pagination-selector";

@Component({
	selector: 'app-paginator',
	templateUrl: './paginator.component.html'
})
export class PaginatorComponent implements OnInit, OnChanges {

	@Input() pagination: Pagination;

	@Input() size: string = 'default';

	@Output() onPaginate = new EventEmitter<Pagination>();

	public pages = [];
	public packages = [];

	public currentPage;
	public firstPage;
	public lastPage;

	public previousPage;
	public nextPage;

	public disabledButtons: any = {};

	public selectors: PaginationSelector[] = [];
	public selectorChildrens: PaginationSelector[] = [];

	public currentSelector: PaginationSelector = null;

	public buttonClass: string = 'btn btn-default';

	public loadMore = false;

	public alphabet = Array.from(Array(26), (e, i) => String.fromCharCode(i + 97));

	private selectorTimeout = null;

	constructor() { }

	ngOnInit() {
		this.createPaginationData();
		this.buttonClasses();
		this.initializeSelector();
	}

	/**
	 * Rework pagination data on pagination settings change event;
	 *
	 * @param changes
	 */
	ngOnChanges(changes: SimpleChanges): void {
		if (typeof changes.pagination !== undefined && changes.pagination.previousValue !== changes.pagination.currentValue) {
			this.createPaginationData();
			this.initializeSelector();
		}
	}

	public initializeSelector(): void {
		this.currentSelector = (this.pagination.getActiveSelector());
		this.selectorChildrens = this.currentSelector.childrens;
	}

	/**
	 * Select page action
	 * @param pageValue
	 */
	public selectPage(pageValue: any) {
		if (this.pagination.mode === PaginationMode.Letters) {
			if (this.pagination.currentLetter === pageValue) {
				return;
			}

			this.pagination.currentLetter = pageValue;
		} else {
			this.pagination.currentPage = pageValue;
		}

		this.pagination.currentPage = pageValue;
		this.onPaginate.next(this.pagination);
		this.createPaginationData();
	}

	/**
	 * @param selector
	 * @param children
	 */
	public setPaginationSelector(selector: PaginationSelector, children: PaginationSelector = null): void {
		clearTimeout(this.selectorTimeout);

		this.createPaginationData();
		this.selectorChildrens = [];
		this.currentSelector = selector;
		this.disabledButtons.loadMore = true;

		for (let selectorKey in this.pagination.selectors) {
			let checkedSelector = this.pagination.selectors[selectorKey];

			if (checkedSelector === selector) {
				this.pagination.selectors[selectorKey].isActive = true;

				if (checkedSelector.childrens) {
					this.selectorChildrens = checkedSelector.childrens;

					for (let childKey in selector.childrens) {
						let checkedChildren = selector.childrens[childKey];

						this.pagination.selectors[selectorKey].childrens[childKey].isActive = ( // selected children (or first in row);
							(children && children === checkedChildren) || (!children && parseInt(childKey) === 0)
						);
					}
				}
			} else {
				this.pagination.selectors[selectorKey].isActive = false;
				this.pagination.selectors[selectorKey].deactivateChildrens();
			}
		}

		this.pagination.reset = true;

		this.selectorTimeout = setTimeout(() => {
			this.onPaginate.next(this.pagination);
			this.disabledButtons.loadMore = false;
		}, (selector.childrens.length === 0 || children) ? 100 : 50000);
	};

	public toggleLoadMore(pck = null): void {
		this.pagination.mode = PaginationMode.LoadMore;
		this.pagination.currentPackage = pck.packageId;
		this.onPaginate.next(this.pagination);
	}

	protected resetPagination(): void {
		this.pages = [];
		this.packages = [];

		this.currentSelector = null;

		this.selectors = [];
		this.selectorChildrens = [];

		this.currentPage = null;
		this.firstPage = null;
		this.lastPage = null;

		this.previousPage = null;
		this.nextPage = null;

		this.loadMore = false;

		this.disabledButtons.loadMore = false;
		this.disabledButtons.previous = false;
		this.disabledButtons.selectPage = false;
		this.disabledButtons.next = false;
	}

	protected buttonClasses(): void {
		switch (this.size) {
			case 'small':
				this.buttonClass = 'btn btn-sm';
				break;

			case 'large':
				this.buttonClass = 'btn btn-lg';
				break;

			default:
				this.buttonClass = 'btn btn-default';
		}
	}

	protected createPaginationData() : void {
		this.resetPagination();

		if (this.pagination.packageSize > 0) {
			for (let packageId = this.pagination.minPackage; packageId <= this.pagination.maxPackage; packageId++) {
				let pck: any = {};

				pck.packageId = packageId;
				pck.packageSize = (packageId * this.pagination.packageSize);

				this.packages.push(pck);
			}
		}

		switch (this.pagination.mode) {
			case PaginationMode.Letters:
				for (let index in this.alphabet) {
					this.pages.push(this.alphabet[index].toUpperCase());
				}

				this.pages.push('Symbol');
				this.currentPage = this.pagination.currentLetter.toUpperCase();
				break;

			case PaginationMode.Numbers:
				for (let x = 1; x <= this.pagination.totalPages; x++)
					this.pages.push(x);

				this.currentPage = this.pagination.currentPage;
				break;

			case PaginationMode.LoadMore:
				this.loadMore = true;
				return;
		}

		let previousPageIndex = null;
		let pageFound = false;

		for (let page of this.pages) {
			if (pageFound) {
				this.nextPage = page;
				break;
			}

			if (page === this.currentPage) {
				pageFound = true;
				this.previousPage = (!previousPageIndex) ? this.currentPage : previousPageIndex;
			}

			previousPageIndex = page;
		}

		if (this.nextPage === null)
			this.nextPage = (this.pagination.mode === PaginationMode.Letters) ? '0' : this.pagination.totalPages;
	}

}
