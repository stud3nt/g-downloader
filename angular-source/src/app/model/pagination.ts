import { PaginationMode } from "../enum/pagination-mode";
import { PaginationSelector } from "./pagination-selector";
import { BaseModel } from "./base/base-model";

export class Pagination extends BaseModel {

	constructor(obj?: any) {
		super();

		Object.assign(this, obj);

		if (obj && typeof obj.selectors !== 'undefined' && obj.selectors) {
			this._selectors = [];

			for (let selectorData of obj.selectors) {
				this._selectors.push(
					new PaginationSelector(selectorData)
				);
			}
		}
	}

	// is pagination active
	private _active: boolean = false;

	// pagination: current page
	private _currentPage: number = 1;

	// pagination: current letter
	private _currentLetter: string = 'A';

	// pagination mode (letters, numbers, load_more etc)
	private _mode: string = PaginationMode.Numbers;

	private _selectors: PaginationSelector[] = [];

	// total pages/max page number
	private _totalPages: number = 1;

	// page number shifter (if necessary)
	private _pageShift: number = 0;

	// min package value
	private _minPackage: number = 1;

	// max package value
	private _maxPackage: number = 10;

	// current selected package
	private _currentPackage: number = 1;

	// number of post/images in one package
	private _packageSize: number = 100;

	// pagination action with reset
	private _reset: boolean = false;

	public getActiveSelector() {
		if (this._selectors) {
			for (let selector of this._selectors) {
				if (selector.isActive) {
					return selector;
				}
			}
		}

		return this._selectors[0];
	}

	get active(): boolean {
		return this._active;
	}

	set active(value: boolean) {
		this._active = value;
	}

	get currentPage(): number {
		return this._currentPage;
	}

	set currentPage(value: number) {
		this._currentPage = value;
	}

	get currentLetter(): string {
		return this._currentLetter;
	}

	set currentLetter(value: string) {
		this._currentLetter = value;
	}

	get mode(): string {
		return this._mode;
	}

	set mode(value: string) {
		this._mode = value;
	}

	get selectors(): PaginationSelector[] {
		return this._selectors;
	}

	set selectors(value: PaginationSelector[]) {
		this._selectors = value;
	}

	get totalPages(): number {
		return this._totalPages;
	}

	set totalPages(value: number) {
		this._totalPages = value;
	}

	get pageShift(): number {
		return this._pageShift;
	}

	set pageShift(value: number) {
		this._pageShift = value;
	}

	get minPackage(): number {
		return this._minPackage;
	}

	set minPackage(value: number) {
		this._minPackage = value;
	}

	get maxPackage(): number {
		return this._maxPackage;
	}

	set maxPackage(value: number) {
		this._maxPackage = value;
	}

	get currentPackage(): number {
		return this._currentPackage;
	}

	set currentPackage(value: number) {
		this._currentPackage = value;
	}

	get packageSize(): number {
		return this._packageSize;
	}

	set packageSize(value: number) {
		this._packageSize = value;
	}

	get reset(): boolean {
		return this._reset;
	}

	set reset(value: boolean) {
		this._reset = value;
	}
}