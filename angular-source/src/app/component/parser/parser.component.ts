import { Component, HostListener, OnInit } from '@angular/core';
import { ActivatedRoute, NavigationEnd, Router } from "@angular/router";
import { ContentHeaderDataService } from "../../service/data/content-header-data.service";
import { ParserService } from "../../service/parser.service";
import { ParserType } from "../../enum/parser-type";
import { ParserNode } from "../../model/parser-node";
import { ParserRequest } from "../../model/parser-request";
import { ParsedFile } from "../../model/parsed-file";
import { ConfigService } from "../../service/config.service";
import { PageLoaderDataService } from "../../service/data/page-loader-data.service";
import { Pagination } from "../../model/pagination";
import { NodesListComponent } from "./nodes-list/nodes-list.component";
import { NodeStatus } from "../../enum/node-status";
import { RouterService } from "../../service/router.service";
import { PaginationMode } from "../../enum/pagination-mode";
import { AuthService } from "../../service/auth.service";
import { ToastrDataService } from "../../service/data/toastr-data.service";
import { Status } from "../../model/status";
import { StatusCode } from "../../enum/status-code";
import { JsonResponse } from "../../model/json-response";
import { WebSocketService } from "../../service/web-socket.service";
import { WebsocketOperation } from "../../enum/websocket-operation";
import { Subscription } from "rxjs";
import { ParserRequestOperation } from "../../model/parser-request-operation";
import { ParserRequestAction } from "../../enum/parser-request-action";

@Component({
	selector: 'app-parser',
	templateUrl: './parser.component.html',
	styleUrls: ['./parser.component.scss']
})

export class ParserComponent implements OnInit {

	@HostListener('document:keydown', ['$event']) onKeydownHandler(event: KeyboardEvent) {
		let tiles = document.getElementsByClassName('tile');

		if (!tiles || tiles.length === 0)
			return;

		let scrollValue = (tiles.item(0).clientHeight);

		scrollValue += scrollValue;
		scrollValue += 24;

		if (event.code === 'ArrowUp') { // UP
			event.preventDefault();
			window.scrollTo(0, ((this.scrollY > scrollValue) ? this.scrollY - scrollValue : 0));
		} else if (event.code === 'ArrowDown') { // DOWN
			event.preventDefault();
			window.scrollTo(0, (this.scrollY + scrollValue));
		}
	}

	// current parser name
	protected parserName: string = '';
	// current node level
	protected nodeLevel: string = '';
	// current node identifier
	protected nodeIdentifier: string = '';

	protected scrollY = 0;

	public parserRequest: ParserRequest = new ParserRequest();
	public currentNode: ParserNode = new ParserNode();

	public runningAction: boolean = false;
	public parserRequestAction: boolean = false;

	public parserBreadcrumbs = [];

	/** Template variables **/
	public toolbarDisplayMode: string = ''; // classes for action belt
	public scrollTopClass: string = ''; // classes for "scroll top" button
	public previousNodeUrl: string = null;
	public nextNodeUrl: string = null;

	public nodesListComponent: NodesListComponent;

	private _filesTemp: ParsedFile[] = [];

	private _routerSub = Subscription.EMPTY;

	private _websocketName: string = 'parser_status';

	constructor(
		public routerService: RouterService,
		private headerData: ContentHeaderDataService,
		private route: ActivatedRoute,
		private router: Router,
		private auth: AuthService,
		private config: ConfigService,
		private parserService: ParserService,
		private toastrService: ToastrDataService,
		private pageLoaderDataService: PageLoaderDataService,
		private webSocketService: WebSocketService
	) {
		this._routerSub = this.router.events.subscribe(value => {
			if (value instanceof NavigationEnd) {
				this.run();
			}
		});
	}

	ngOnInit() {
		this.run();
	};

	ngOnDestroy() {
		this._routerSub.unsubscribe();
		window.removeEventListener('scroll', this.scrollEvent, true);
	}

	public run(): void {
		if (this.runningAction === true)
			return;

		window.addEventListener('scroll', this.scrollEvent, true);

		this.runningAction = true;
		this.parserBreadcrumbs = [];
		this.previousNodeUrl = null;
		this.nextNodeUrl = null;

		this.scrollEvent();

		this.determineParserData();
		this.initializeParserRequestObject();
		this.setHeaderData();

		this.sendParserRequest();

		setTimeout(() => {
			this.runningAction = false;
		}, 200);
	}

	/**
	 * Downloads data again for current node;
	 *
	 * @param reloadCache - true if reloading is 'hard' (cache must be refreshed too)
	 */
	public reopenCurrentNode(reloadCache: boolean = false): void {
		this.parserRequest.clearParsedData();
		this.parserRequest.clearTokens();
		this.parserRequest.ignoreCache = reloadCache;
		this.sendParserRequest(() => {
			window.scrollTo(0, 0);
		});
	};

	/**
	 * Update node state
	 *
	 * @param node: ParserNode
	 */
	public updateCurrentNode(node: ParserNode): void {
		this.parserRequest.currentNode = node;

		this.parserService.updateNode(node).subscribe((node: ParserNode) => {
            node.removeStatus(NodeStatus.Waiting);

            this.parserRequest.currentNode = node;

            setTimeout(() => {
                this.currentNode = node;
            }, 10)

            this.toastrService.addSuccess('SUCCESS', 'Node has been updated.', 8);
		}, (error) => {
            node.removeStatus(NodeStatus.Waiting);
            this.toastrService.addError('ERROR', error.message);
		});
	};

	public parserRequestChangeAction(operation: ParserRequestOperation): void {
		switch (operation.action) {
			case ParserRequestAction.CurrentNodeUpdate:
				this.updateCurrentNode(operation.parserRequest.currentNode);
				break;

			case ParserRequestAction.HardReload:
				this.reopenCurrentNode(true);
				break;

			case ParserRequestAction.Pagination:
				this.changeNodePage(operation.parserRequest.pagination);
				break;

			case ParserRequestAction.ParserRequestUpdate:
				this.parserRequest = operation.parserRequest;
				break;
		}
	}

	/**
	 * Determines classes for current node;
	 *
	 * @param status
	 */
	public getCurrentNodeButtonClass(status: string): string {
		this.nodesListComponent = new NodesListComponent(this.parserService, this.toastrService, this.routerService);

		return this.nodesListComponent.getNodeButtonClass(
			this.parserRequest.currentNode, status
		);
	}

	public scrollTop(): void {
		window.scrollTo(0, 0);
	};

	/**
	 * Changes page of currently viewed node.
	 *
	 * @param pagination - pagination settings;
	 */
	public changeNodePage(pagination: Pagination): void {
		this._filesTemp = (pagination.mode === PaginationMode.LoadMore) // new files will be added to current;
			? this.parserRequest.files
			: null;

		if (pagination.reset) {
			this._filesTemp = null;
			this.parserRequest.clearTokens();
			pagination.reset = false;
		}

		this.parserRequestAction = false;
		this.parserRequest.pagination = pagination;
	 	this.parserRequest.clearParsedData();
		this.sendParserRequest();
	};

	/**
	 * Scrolling event function - saves scroll position and runs classes function for action belt;
	 * @param event
	 */
	protected scrollEvent = (event: any = null): void => {
		this.scrollY = window.scrollY;
		this.toolbarDisplayMode = (this.scrollY > 80) ? 'fixed' : 'standard';
	};

	/**
	 * Creates ParserRequest model with initial settings;
	 */
	private initializeParserRequestObject() : void {
		this.parserRequest = new ParserRequest();
		this.parserRequest.currentNode = this.initializeParserNodeObject();
		this.parserRequest.apiToken = this.auth.user.apiToken;
	};

	/**
	 * Creates initial ParserNodeObject with absolutely basic datas;
	 */
	private initializeParserNodeObject() : ParserNode {
		let node = new ParserNode();

		node.parser = this.parserName;
		node.level = this.nodeLevel;
		node.identifier = this.nodeIdentifier;

		return node;
	};

	private sendWebsocketRequestTimeout = null;
	private disconnectWebsockedTimeout = null;
	private createWebsocketListenerTimeout = null;
	private websockedRequestRecursiveTimeout = null;

	/**
	 * Sends data to parser API
	 */
	private sendParserRequest(successFunction: () => any = null, errorFunction: () => any = null, completeFunction: () => any = null): void {
	    if (this.parserRequestAction === true) // only one action at same time
			return;
		else
			this.parserRequestAction = true;

		let parserRequestCopy = this.parserRequest;
		parserRequestCopy.clearParsedData();

		setTimeout(() => {
			this.parserRequest.files = this._filesTemp;
		}, 20);

		clearTimeout(this.sendWebsocketRequestTimeout);

		this.sendWebsocketRequestTimeout = setTimeout(() => {
			this.sendWebsocketRequest();
		}, 200);

		this.parserService.sendParserActionRequest(parserRequestCopy).subscribe((response : ParserRequest) => {
            this.parserRequestAction = false;

			if (typeof response.currentNode !== 'undefined') {
				this.parserRequest = response;
				this.currentNode = response.currentNode;
				this.previousNodeUrl = (response.previousNode) ? this.routerService.generateNodeUrl(response.previousNode) : null;
				this.nextNodeUrl = (response.nextNode) ? this.routerService.generateNodeUrl(response.nextNode) : null;

				if (this._filesTemp) // adding new files at end of list;
					this.parserRequest.files = [...this._filesTemp, ...this.parserRequest.files];

				this.parserRequest.clearFilesDuplicates();

				if (successFunction)
					successFunction();
			}
		}, (error) => {
			this.pageLoaderDataService.hide();
			this.parserRequestAction = false;

			if (errorFunction)
				errorFunction();
		}, () => {
			this.pageLoaderDataService.hide();
			this.parserRequestAction = false;

			if (completeFunction)
				completeFunction();
		});

		clearTimeout(this.disconnectWebsockedTimeout);

		this.disconnectWebsockedTimeout = setTimeout(() => {
			this.webSocketService.disconnect(this._websocketName);

			clearTimeout(this.createWebsocketListenerTimeout);

			this.createWebsocketListenerTimeout = setTimeout(() => {
				this.webSocketService.createListener(
					this._websocketName,(response) => {
						let recursive = true;

						if (typeof response === 'object') {
							let jsonResponse = new JsonResponse(response);

							if (jsonResponse.success()) {
								let status = new Status(jsonResponse.data);

								switch (status.code) {
									case StatusCode.NoEffect:
									    this.parserRequestAction = true;
									    break;

									case StatusCode.OperationStarted:
										this.pageLoaderDataService.show(true).setProgress(status.progress);
										this.parserRequestAction = true;
										break;

									case StatusCode.OperationInProgress:
										this.pageLoaderDataService.show().setProgress(status.progress);
										break;

									case StatusCode.OperationEnded:
										this.pageLoaderDataService.hide(800);
										this.parserRequestAction = false;
										recursive = false;
										break;

									default:
										recursive = false;
								}
							}
						} else {
							switch (response) {
								case 'CONNECTION OPEN':
									break;

								default:
									console.log(response);
									this.pageLoaderDataService.hide();
							}
						}

						if (recursive && this.parserRequestAction) {
						    clearTimeout(this.websockedRequestRecursiveTimeout);

                            this.websockedRequestRecursiveTimeout = setTimeout(() => {
                                this.sendWebsocketRequest()
                            }, 400);
                        }

					},
					(error) => {
						this.toastrService.addError('WEBSOCKET ERROR', error.message);
						console.log(error);
					},
					() => {
						this.parserRequestAction = false;
					}
				);
			});
		}, 50);
	}

	private sendWebsocketRequest(): void {
		this.webSocketService.sendRequest(
			this._websocketName,
			WebsocketOperation.ParserProgress,
			this.auth.user.apiToken,
			this.parserRequest
		);
	}

	/**
	 * Determines parser name based by URL address;
	 */
	private determineParserData() : void {
		let urlArray = this.router.url.split('/');

		urlArray.forEach((value, index) => {
			if (value === 'parsers') {
				this.parserName = urlArray[(index+1)];
				this.nodeLevel = (typeof urlArray[(index+2)] !== 'undefined') ? urlArray[(index+2)] : null;
				this.nodeIdentifier = (typeof urlArray[(index+3)] !== 'undefined') ? urlArray[(index+3)] : null;
			}
		});
	}

	/**
	 * Sets data for content-header
	 *
	 * IMPORTANT: data musts set AFTER initialize parser board data
	 */
	private setHeaderData() : void {
		let parserEnumData = ParserType.getData();

		this.headerData.setElement('title1', 'Parser');
		this.headerData.setElement('title2', 'Scrapping images and movies');
		this.headerData.clearBreadcrumbs();
		this.headerData.addBreadcrumb({
			route: null,
			routeParams: {'parserName':this.parserName},
			label: 'Parsers',
			icon: 'fa-dashboard'
		});
		this.headerData.addBreadcrumb({
			route: 'app_parser',
			routeParams: {'parserName':this.parserName},
			label: (typeof parserEnumData[this.parserName] !== 'undefined')
				? parserEnumData[this.parserName]
				: null,
			icon: 'fa-dashboard'
		});
	}

}
